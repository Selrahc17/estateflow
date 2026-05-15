<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\EstateNotification;
use App\Models\Reservation;
use App\Models\SiteViewingSchedule;
use App\Models\User;
use Illuminate\Http\Request;

class SiteViewingController extends Controller
{
    // Client: show schedule form for a reservation
    public function create(Reservation $reservation)
    {
        $clientRecord = Client::where('user_id', auth()->id())->first();

        if (!$clientRecord || $reservation->client_id !== $clientRecord->id) {
            abort(403);
        }

        $existing = SiteViewingSchedule::where('reservation_id', $reservation->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        return view('site-viewing.create', compact('reservation', 'existing'));
    }

    // Client: submit schedule
    public function store(Request $request, Reservation $reservation)
    {
        $clientRecord = Client::where('user_id', auth()->id())->first();

        if (!$clientRecord || $reservation->client_id !== $clientRecord->id) {
            abort(403);
        }

        $request->validate([
            'preferred_date' => 'required|date|after:today',
            'preferred_time' => 'required',
            'notes'          => 'nullable|string|max:500',
        ]);

        $schedule = SiteViewingSchedule::create([
            'reservation_id' => $reservation->id,
            'client_id'      => $clientRecord->id,
            'preferred_date' => $request->preferred_date,
            'preferred_time' => $request->preferred_time,
            'notes'          => $request->notes,
            'status'         => 'pending',
        ]);

        // Notify all agents and admins
        $notifyUsers = User::whereIn('role', ['admin', 'agent'])->where('is_active', true)->get();
        foreach ($notifyUsers as $user) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $user->id,
                'type'            => 'site_viewing_request',
                'title'           => 'Site Viewing Request — ' . $clientRecord->full_name,
                'message'         => $clientRecord->full_name . ' requested a site viewing for ' .
                    ($reservation->property->title ?? 'a property') . ' on ' .
                    \Carbon\Carbon::parse($request->preferred_date)->format('M d, Y') . ' at ' .
                    \Carbon\Carbon::parse($request->preferred_time)->format('g:i A') . '.',
                'data'            => json_encode(['schedule_id' => $schedule->id, 'reservation_id' => $reservation->id]),
            ]);
        }

        return redirect()->route('client.reservations')
            ->with('success', 'Site viewing request submitted! An agent will confirm your schedule shortly.');
    }

    // Agent/Admin: list all schedules
    public function index(Request $request)
    {
        $query = SiteViewingSchedule::with(['reservation.property', 'client', 'confirmedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules     = $query->latest()->paginate(15)->withQueryString();
        $pendingCount  = SiteViewingSchedule::where('status', 'pending')->count();
        $confirmedCount = SiteViewingSchedule::where('status', 'confirmed')->count();

        return view('site-viewing.index', compact('schedules', 'pendingCount', 'confirmedCount'));
    }

    // Agent/Admin: confirm schedule
    public function confirm(SiteViewingSchedule $siteViewing)
    {
        $siteViewing->update([
            'status'       => 'confirmed',
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);

        $this->notifyClient($siteViewing, 'confirmed');

        return back()->with('success', 'Site viewing confirmed.');
    }

    // Agent/Admin: cancel schedule
    public function cancel(SiteViewingSchedule $siteViewing)
    {
        $siteViewing->update(['status' => 'cancelled']);
        $this->notifyClient($siteViewing, 'cancelled');

        return back()->with('success', 'Site viewing cancelled.');
    }

    // Agent/Admin: mark as completed
    public function complete(SiteViewingSchedule $siteViewing)
    {
        $siteViewing->update(['status' => 'completed']);
        $this->notifyClient($siteViewing, 'completed');

        return back()->with('success', 'Site viewing marked as completed.');
    }

    private function notifyClient(SiteViewingSchedule $schedule, string $status)
    {
        $clientUser = User::find($schedule->client->user_id ?? null);
        if (!$clientUser) return;

        $messages = [
            'confirmed' => 'Your site viewing on ' . $schedule->preferred_date->format('M d, Y') . ' at ' .
                \Carbon\Carbon::parse($schedule->preferred_time)->format('g:i A') . ' has been confirmed!',
            'cancelled' => 'Your site viewing scheduled on ' . $schedule->preferred_date->format('M d, Y') . ' has been cancelled. Please reschedule.',
            'completed' => 'Your site viewing on ' . $schedule->preferred_date->format('M d, Y') . ' has been marked as completed.',
        ];

        EstateNotification::create([
            'notifiable_type' => User::class,
            'notifiable_id'   => $clientUser->id,
            'type'            => 'site_viewing_' . $status,
            'title'           => 'Site Viewing ' . ucfirst($status),
            'message'         => $messages[$status],
            'data'            => json_encode(['schedule_id' => $schedule->id]),
        ]);

        // Also notify admins
        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        foreach ($admins as $admin) {
            if ($admin->id === auth()->id()) continue;
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'type'            => 'site_viewing_' . $status,
                'title'           => 'Site Viewing ' . ucfirst($status) . ' — ' . ($schedule->client->full_name ?? ''),
                'message'         => $messages[$status],
                'data'            => json_encode(['schedule_id' => $schedule->id]),
            ]);
        }
    }
}
