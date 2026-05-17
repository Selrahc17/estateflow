<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\EstateNotification;
use App\Models\User;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Document;
use App\Models\Message;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isClient = $user && $user->isClient();

        $featuredProperties = Property::with('propertyType')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->where('status', 'available')
            ->latest()
            ->take(6)
            ->get();

        $latestProperties = Property::with('propertyType')
            ->where('is_active', true)
            ->where('status', 'available')
            ->latest()
            ->take(8)
            ->get();

        $propertyTypes = PropertyType::where('is_active', true)->get();

        $totalProperties  = Property::where('is_active', true)->count();
        $availableCount   = Property::where('status', 'available')->count();

        // Client-specific data
        $clientRecord = null;
        $myReservations = collect();
        $myTotalReservations = 0;
        $myPayments = collect();
        $myTotalPaid = 0;
        $myDocuments = collect();
        $unreadMessages = 0;

        if ($isClient) {
            $clientRecord = Client::where('user_id', $user->id)->first();

            if ($clientRecord) {
                // Reservations
                $myReservations = Reservation::with(['property', 'agent'])
                    ->where('client_id', $clientRecord->id)
                    ->latest()
                    ->take(5)
                    ->get();
                $myTotalReservations = Reservation::where('client_id', $clientRecord->id)->count();

                // Payments
                $myPayments = Payment::with(['reservation.property'])
                    ->where('client_id', $clientRecord->id)
                    ->latest()
                    ->take(5)
                    ->get();
                $myTotalPaid = Payment::where('client_id', $clientRecord->id)
                    ->where('status', 'completed')
                    ->sum('amount');

                // Documents
                $reservationIds = Reservation::where('client_id', $clientRecord->id)->pluck('id');
                $myDocuments = Document::where(function ($q) use ($clientRecord, $reservationIds) {
                    $q->where(function ($q2) use ($clientRecord) {
                        $q2->where('documentable_type', Client::class)
                           ->where('documentable_id', $clientRecord->id);
                    })->orWhere(function ($q2) use ($reservationIds) {
                        $q2->where('documentable_type', Reservation::class)
                           ->whereIn('documentable_id', $reservationIds);
                    });
                })
                ->latest()
                ->take(5)
                ->get();

                // Unread messages
                $unreadMessages = Message::where('to_user_id', $user->id)->whereNull('read_at')->count();
            }
        }

        return view('home', compact(
            'featuredProperties', 'latestProperties',
            'propertyTypes', 'totalProperties', 'availableCount',
            'isClient', 'clientRecord',
            'myReservations', 'myTotalReservations',
            'myPayments', 'myTotalPaid',
            'myDocuments', 'unreadMessages'
        ));
    }

    public function browse(Request $request)
    {
        $query = Property::with('propertyType')->where('is_active', true);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        // Property type checkboxes (multiple)
        if ($request->filled('types')) {
            $query->whereIn('property_type_id', (array) $request->types);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }

        // Bathroom filter
        if ($request->filled('bathrooms')) {
            $query->where('bathrooms', '>=', $request->bathrooms);
        }

        // Location-based filtering (specific areas like Oogong, Gatid)
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        $properties   = $query->latest()->paginate(12)->withQueryString();
        $propertyTypes = PropertyType::where('is_active', true)->get();

        // Get distinct locations from properties for the location filter
        $locations = Property::where('is_active', true)
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->pluck('location')
            ->map(function ($loc) {
                $parts = explode(',', $loc);
                return trim($parts[0]);
            })
            ->unique()
            ->sort()
            ->values();

        return view('home-browse', compact('properties', 'propertyTypes', 'locations'));
    }

    public function property(Property $property)
    {
        $property->load(['propertyType', 'reservations']);

        $similar = Property::with('propertyType')
            ->where('property_type_id', $property->property_type_id)
            ->where('id', '!=', $property->id)
            ->where('is_active', true)
            ->take(3)
            ->get();

        return view('home-property', compact('property', 'similar'));
    }

    public function aiRecommend(Request $request)
    {
        $request->validate([
            'monthly_income'    => 'required|numeric|min:1',
            'family_size'       => 'required|string',
            'payment_scheme'    => 'required|in:cash,pagibig',
            'employment_type'   => 'nullable|string',
        ]);

        $properties = Property::with('propertyType')
            ->where('is_active', true)
            ->get(['id', 'title', 'price', 'bedrooms', 'bathrooms', 'area_sqm', 'location', 'status', 'property_type_id']);

        $propertyList = $properties->map(fn($p) => [
            'id'       => $p->id,
            'title'    => $p->title,
            'price'    => (float) $p->price,
            'bedrooms' => $p->bedrooms,
            'area_sqm' => (float) $p->area_sqm,
            'location' => $p->location,
            'status'   => $p->status,
            'type'     => $p->propertyType?->name,
        ])->values()->toArray();

        $employmentNote = $request->payment_scheme === 'pagibig' && $request->filled('employment_type')
            ? 'Employment type: ' . $request->employment_type . '.'
            : '';

        $prompt = <<<PROMPT
You are a Philippine real estate advisor for EstateFlow.
Buyer profile:
- Monthly income: ₱{$request->monthly_income}
- Family size: {$request->family_size}
- Payment scheme: {$request->payment_scheme}
{$employmentNote}

Properties (JSON):
{$this->chunkProperties($propertyList)}

For each property, assign a match percentage (0-100) based on affordability (price vs income), family size fit (bedrooms), and payment scheme compatibility. A property is affordable if its price is roughly ≤ 120× monthly income for Pag-IBIG or ≤ 60× for cash.

Respond ONLY with a valid JSON array, no markdown, no explanation:
[{"id": 1, "match": 85}, {"id": 2, "match": 42}, ...]
PROMPT;

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.groq.key'),
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'       => 'llama-3.1-8b-instant',
                    'temperature' => 0.1,
                    'max_tokens'  => 1024,
                    'messages'    => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                ]);

            if (!$response->successful()) {
                \Log::error('Groq API error', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'api_error', 'detail' => $response->body()], 502);
            }

            $text = $response->json('choices.0.message.content', '');
            $text = trim(preg_replace('/^```[\w]*\n?|\n?```$/m', '', trim($text)));
            $matches = json_decode($text, true);

            if (!is_array($matches)) {
                \Log::error('Groq parse failed', ['raw' => $text]);
                return response()->json(['error' => 'parse_failed', 'raw' => $text], 422);
            }

            return response()->json(['matches' => $matches]);
        } catch (\Exception $e) {
            \Log::error('Groq exception', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'api_failed', 'detail' => $e->getMessage()], 503);
        }
    }

    private function chunkProperties(array $properties): string
    {
        // Limit to 20 properties to stay within free tier token limits
        return json_encode(array_slice($properties, 0, 20));
    }

    public function inquiry(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'phone'   => 'nullable|string|max:30',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'subject', 'message']);

        // Auto-create a lead from inquiry
        Lead::create([
            'name'   => $data['name'],
            'email'  => $data['email'],
            'phone'  => $data['phone'] ?? null,
            'source' => 'website_inquiry',
            'status' => 'new',
            'notes'  => 'Subject: ' . $data['subject'] . "\n" . $data['message'],
        ]);

        // Email all admins
        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        foreach ($admins as $admin) {
            Mail::send('emails.inquiry', $data, function ($m) use ($admin, $data) {
                $m->to($admin->email, $admin->name)
                  ->subject('New Inquiry: ' . $data['subject']);
            });

            // In-app notification
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'type'            => 'inquiry',
                'title'           => 'New Inquiry from ' . $data['name'],
                'message'         => $data['message'],
                'data'            => json_encode(['email' => $data['email'], 'phone' => $data['phone'] ?? null]),
            ]);
        }

        return redirect()->route('home', ['#contact'])
            ->with('inquiry_sent', true);
    }
}