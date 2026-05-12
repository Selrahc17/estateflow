<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Collection;

class PropertyRecommendationService
{
    /**
     * Score weights (must total 100)
     */
    private const WEIGHTS = [
        'budget'   => 35,
        'location' => 25,
        'type'     => 20,
        'bedrooms' => 15,
        'financing'=> 5,
    ];

    /**
     * Financing option → compatible property statuses/types
     */
    private const FINANCING_COMPATIBLE = [
        'pagibig'   => ['available', 'under_construction'],
        'bank_loan' => ['available'],
        'cash'      => ['available', 'reserved'],
        'in_house'  => ['available', 'under_construction'],
    ];

    /**
     * Family size → recommended minimum bedrooms
     */
    private const FAMILY_BEDROOMS = [
        1 => 1, // single
        2 => 1, // couple
        3 => 2, // small family
        4 => 2,
        5 => 3,
        6 => 3,
        7 => 4,
    ];

    public static function recommend(array $preferences): Collection
    {
        $budget     = (float) ($preferences['budget'] ?? 0);
        $location   = $preferences['location'] ?? '';
        $typeId     = $preferences['property_type_id'] ?? null;
        $financing  = $preferences['financing'] ?? 'cash';
        $familySize = (int) ($preferences['family_size'] ?? 2);

        $minBedrooms = self::FAMILY_BEDROOMS[min($familySize, 7)] ?? 1;

        // Load all active available properties
        $properties = Property::with('propertyType')
            ->where('is_active', true)
            ->whereIn('status', ['available', 'under_construction'])
            ->get();

        return $properties->map(function (Property $property) use (
            $budget, $location, $typeId, $financing, $minBedrooms, $familySize
        ) {
            $score  = 0;
            $breakdown = [];

            // --- Budget Score (35pts) ---
            if ($budget > 0 && $property->price > 0) {
                $diff    = abs($property->price - $budget) / $budget;
                if ($diff <= 0.05) {
                    $pts = 35; // within 5%
                } elseif ($diff <= 0.15) {
                    $pts = 28; // within 15%
                } elseif ($diff <= 0.30) {
                    $pts = 18; // within 30%
                } elseif ($property->price <= $budget) {
                    $pts = 10; // under budget
                } else {
                    $pts = 0;  // over budget
                }
                $score += $pts;
                $breakdown['budget'] = $pts;
            }

            // --- Location Score (25pts) ---
            if ($location && $property->location) {
                $propertyLoc = strtolower($property->location);
                $prefLoc     = strtolower($location);
                if ($propertyLoc === $prefLoc) {
                    $pts = 25;
                } elseif (str_contains($propertyLoc, $prefLoc) || str_contains($prefLoc, $propertyLoc)) {
                    $pts = 15;
                } else {
                    $pts = 0;
                }
                $score += $pts;
                $breakdown['location'] = $pts;
            } else {
                // No location preference — give partial credit
                $score += 12;
                $breakdown['location'] = 12;
            }

            // --- Property Type Score (20pts) ---
            if ($typeId && $property->property_type_id == $typeId) {
                $score += 20;
                $breakdown['type'] = 20;
            } elseif (!$typeId) {
                $score += 10;
                $breakdown['type'] = 10;
            } else {
                $breakdown['type'] = 0;
            }

            // --- Bedrooms / Family Size Score (15pts) ---
            if ($property->bedrooms !== null) {
                if ($property->bedrooms >= $minBedrooms) {
                    $diff = $property->bedrooms - $minBedrooms;
                    $pts  = $diff === 0 ? 15 : ($diff === 1 ? 10 : 5);
                } else {
                    $pts = 0;
                }
                $score += $pts;
                $breakdown['bedrooms'] = $pts;
            } else {
                $score += 7;
                $breakdown['bedrooms'] = 7;
            }

            // --- Financing Score (5pts) ---
            $compatibleStatuses = self::FINANCING_COMPATIBLE[$financing] ?? ['available'];
            if (in_array($property->status, $compatibleStatuses)) {
                $score += 5;
                $breakdown['financing'] = 5;
            } else {
                $breakdown['financing'] = 0;
            }

            $property->match_score     = min($score, 100);
            $property->match_breakdown = $breakdown;
            $property->match_label     = self::matchLabel($score);

            return $property;
        })
        ->filter(fn($p) => $p->match_score >= 20) // exclude very poor matches
        ->sortByDesc('match_score')
        ->take(6)
        ->values();
    }

    private static function matchLabel(int $score): string
    {
        return match(true) {
            $score >= 85 => 'Excellent Match',
            $score >= 70 => 'Great Match',
            $score >= 55 => 'Good Match',
            $score >= 40 => 'Fair Match',
            default      => 'Partial Match',
        };
    }
}
