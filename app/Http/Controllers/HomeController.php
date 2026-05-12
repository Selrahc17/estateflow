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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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