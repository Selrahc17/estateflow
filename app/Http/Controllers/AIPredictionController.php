<?php

namespace App\Http\Controllers;

use App\Models\AIPrediction;
use App\Models\Property;
use App\Models\Project;
use Illuminate\Http\Request;

class AIPredictionController extends Controller
{
    public function index(Request $request)
    {
        $query = AIPrediction::with('createdBy');

        if ($request->filled('prediction_type')) {
            $query->where('prediction_type', $request->prediction_type);
        }

        if ($request->filled('predictable_type')) {
            $morphMap = ['property' => Property::class, 'project' => Project::class];
            if (isset($morphMap[$request->predictable_type])) {
                $query->where('predictable_type', $morphMap[$request->predictable_type]);
            }
        }

        $predictions  = $query->latest()->paginate(15)->withQueryString();
        $totalCount   = AIPrediction::count();
        $propertyCount = AIPrediction::where('predictable_type', Property::class)->count();
        $projectCount  = AIPrediction::where('predictable_type', Project::class)->count();

        return view('ai-predictions.index', compact('predictions', 'totalCount', 'propertyCount', 'projectCount'));
    }

    public function create()
    {
        $properties = Property::where('is_active', true)->get();
        $projects   = Project::orderBy('name')->get();
        return view('ai-predictions.create', compact('properties', 'projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'predictable_type' => 'required|in:property,project',
            'predictable_id'   => 'required|integer',
            'prediction_type'  => 'required|string|max:100',
            'model_version'    => 'nullable|string|max:50',
        ]);

        $morphMap = ['property' => Property::class, 'project' => Project::class];
        $model    = $morphMap[$request->predictable_type]::findOrFail($request->predictable_id);

        // Generate prediction based on type and model
        [$predictedValue, $confidenceScore, $predictionData, $inputFeatures] =
            $this->generatePrediction($request->prediction_type, $model, $request->predictable_type);

        AIPrediction::create([
            'predictable_type' => $morphMap[$request->predictable_type],
            'predictable_id'   => $request->predictable_id,
            'prediction_type'  => $request->prediction_type,
            'predicted_value'  => $predictedValue,
            'confidence_score' => $confidenceScore,
            'prediction_data'  => $predictionData,
            'input_features'   => $inputFeatures,
            'model_version'    => $request->model_version ?? 'v1.0',
            'created_by'       => auth()->id(),
        ]);

        return redirect()->route('ai-predictions.index')->with('success', 'Prediction generated successfully.');
    }

    public function show(AIPrediction $aiPrediction)
    {
        $aiPrediction->load(['predictable', 'createdBy']);
        return view('ai-predictions.show', compact('aiPrediction'));
    }

    public function destroy(AIPrediction $aiPrediction)
    {
        $aiPrediction->delete();
        return back()->with('success', 'Prediction deleted.');
    }

    private function generatePrediction(string $type, $model, string $modelType): array
    {
        if ($modelType === 'property') {
            return match($type) {
                'price_prediction' => $this->predictPropertyPrice($model),
                'market_analysis'  => $this->analyzeMarket($model),
                default            => $this->generalRecommendation($model),
            };
        }

        return $this->analyzeProjectProgress($model);
    }

    private function predictPropertyPrice(Property $property): array
    {
        // Comparables: same type, similar area (±30%), same location keyword
        $comparables = Property::where('property_type_id', $property->property_type_id)
            ->where('id', '!=', $property->id)
            ->where('price', '>', 0)
            ->when($property->area_sqm, fn($q) =>
                $q->whereBetween('area_sqm', [$property->area_sqm * 0.7, $property->area_sqm * 1.3])
            )
            ->get();

        $avgComparablePrice = $comparables->avg('price') ?? $property->price;

        // Score completeness of property data (0–5 factors)
        $dataScore = collect([
            $property->area_sqm, $property->bedrooms, $property->bathrooms,
            $property->location, $property->property_type_id,
        ])->filter()->count(); // 0–5

        // Weighted predicted price: 60% comparables avg, 40% own price adjusted by area
        $areaAdjustment = $property->area_sqm
            ? (($property->area_sqm - ($comparables->avg('area_sqm') ?? $property->area_sqm)) / max($property->area_sqm, 1)) * 0.1
            : 0;

        $predicted = round(
            ($avgComparablePrice * 0.6) + ($property->price * (1 + $areaAdjustment) * 0.4),
            2
        );

        // Confidence: based on comparable count and data completeness
        $sampleScore    = min($comparables->count() * 8, 40); // up to 40 pts from sample size
        $completeness   = ($dataScore / 5) * 40;              // up to 40 pts from data completeness
        $baseConfidence = 20;                                  // base 20 pts
        $confidence     = min(round($baseConfidence + $sampleScore + $completeness, 1), 97);

        return [
            $predicted,
            $confidence,
            [
                'current_price'      => $property->price,
                'predicted_price'    => $predicted,
                'price_change'       => round($predicted - $property->price, 2),
                'change_percent'     => round((($predicted - $property->price) / max($property->price, 1)) * 100, 2),
                'comparables_used'   => $comparables->count(),
                'avg_comparable'     => round($avgComparablePrice, 2),
                'recommendation'     => $predicted > $property->price
                    ? 'Comparable properties suggest upward price potential.'
                    : 'Comparable properties suggest current price is at or above market.',
            ],
            [
                'area_sqm'  => $property->area_sqm,
                'bedrooms'  => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
                'location'  => $property->location,
                'status'    => $property->status,
            ],
        ];
    }

    private function analyzeMarket(Property $property): array
    {
        $sameType = Property::where('property_type_id', $property->property_type_id)->get();
        $avgPrice = $sameType->avg('price') ?? $property->price;

        // Demand: ratio of reserved+sold vs total of same type
        $total    = max($sameType->count(), 1);
        $active   = $sameType->whereIn('status', ['reserved', 'sold'])->count();
        $demandRatio = $active / $total;

        $demand = match(true) {
            $demandRatio >= 0.75 => 'Very High',
            $demandRatio >= 0.50 => 'High',
            $demandRatio >= 0.25 => 'Moderate',
            default              => 'Low',
        };

        // Recent reservations in last 90 days for this type
        $recentReservations = \App\Models\Reservation::whereHas('property',
            fn($q) => $q->where('property_type_id', $property->property_type_id)
        )->where('created_at', '>=', now()->subDays(90))->count();

        // Confidence: based on sample size
        $confidence = min(round(30 + ($total * 5) + ($recentReservations * 3), 1), 95);

        $vsMarket = $avgPrice > 0
            ? round((($property->price - $avgPrice) / $avgPrice) * 100, 2)
            : 0;

        return [
            $avgPrice,
            $confidence,
            [
                'market_avg_price'     => round($avgPrice, 2),
                'demand_level'         => $demand,
                'demand_ratio_percent' => round($demandRatio * 100, 1),
                'recent_reservations'  => $recentReservations,
                'total_same_type'      => $total,
                'vs_market'            => $vsMarket . '%',
                'recommendation'       => "Demand is {$demand} ({$recentReservations} reservations in 90 days). "
                    . "This property is " . ($vsMarket >= 0 ? "+{$vsMarket}%" : "{$vsMarket}%")
                    . " vs market average of ₱" . number_format($avgPrice, 0) . ".",
            ],
            [
                'property_type_id' => $property->property_type_id,
                'location'         => $property->location,
                'current_price'    => $property->price,
            ],
        ];
    }

    private function analyzeProjectProgress(Project $project): array
    {
        $budgetUsed = $project->budget > 0
            ? round(($project->actual_cost / $project->budget) * 100, 2)
            : 0;

        // Days elapsed vs total planned duration
        $startDate  = $project->start_date;
        $endDate    = $project->estimated_completion_date;
        $timeProgress = 0;
        if ($startDate && $endDate) {
            $totalDays   = max($startDate->diffInDays($endDate), 1);
            $elapsedDays = min($startDate->diffInDays(now()), $totalDays);
            $timeProgress = round(($elapsedDays / $totalDays) * 100, 1);
        }

        // On track: completion % should be >= time elapsed %
        $onTrack = $project->completion_percentage >= ($timeProgress - 10);

        // Budget on track: budget used % should not exceed completion % by more than 15
        $budgetOnTrack = $budgetUsed <= ($project->completion_percentage + 15);

        // Confidence: based on progress log count and milestone data
        $logCount       = $project->progressLogs()->count();
        $milestoneCount = $project->milestones()->count();
        $confidence     = min(round(40 + ($logCount * 5) + ($milestoneCount * 4), 1), 96);

        // Estimated days remaining
        $daysRemaining = $endDate ? max(now()->diffInDays($endDate, false), 0) : null;

        return [
            $project->completion_percentage,
            $confidence,
            [
                'completion_percentage' => $project->completion_percentage,
                'time_elapsed_percent'  => $timeProgress,
                'budget_used_percent'   => $budgetUsed,
                'on_schedule'           => $onTrack,
                'budget_on_track'       => $budgetOnTrack,
                'days_remaining'        => $daysRemaining,
                'progress_logs'         => $logCount,
                'milestones'            => $milestoneCount,
                'recommendation'        => collect([
                    !$onTrack       ? 'Project is behind schedule — completion % lags time elapsed.' : null,
                    !$budgetOnTrack ? 'Budget consumption exceeds progress — review resource allocation.' : null,
                    $onTrack && $budgetOnTrack ? 'Project is on track with both schedule and budget.' : null,
                ])->filter()->implode(' '),
                'estimated_completion'  => $endDate?->format('M d, Y') ?? 'Not set',
            ],
            [
                'budget'      => $project->budget,
                'actual_cost' => $project->actual_cost,
                'completion'  => $project->completion_percentage,
                'status'      => $project->status,
            ],
        ];
    }

    private function generalRecommendation($model): array
    {
        return [null, 50, ['recommendation' => 'General analysis completed.'], []];
    }
}
