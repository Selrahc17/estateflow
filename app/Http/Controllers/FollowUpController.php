<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Client;
use App\Models\EstateNotification;
use App\Models\FollowUpSchedule;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $user        = auth()->user();
        $agentRecord = Agent::where('user_id', $user->id)->first();

        $query = FollowUpSchedule::with(['client', 'agent', 'reservation.property']);

        if (!$user->isAdmin() && $agentRecord) {
            $query->where('agent_id', $agentRecord->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->whereHas('client', fn($q) =>
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
            );
        }

        $schedules    = $query->orderBy('follow_up_date')->paginate(15)->withQueryString();
        $pendingCount = FollowUpSchedule::when(!$user->isAdmin() && $agentRecord, fn($q) =>
            $q->where('agent_id', $agentRecord->id)
        )->where('status', 'pending')->count();

        $todayCount = FollowUpSchedule::when(!$user->isAdmin() && $agentRecord, fn($q) =>
            $q->where('agent_id', $agentRecord->id)
        )->where('status', 'pending')->whereDate('follow_up_date', today())->count();

        return view('follow-ups.index', compact('schedules', 'pendingCount', 'todayCount', 'agentRecord'));
    }

    public function create(Request $request)
    {
        $user        = auth()->user();
        $agentRecord = Agent::where('user_id', $user->id)->first();

        $clients      = Client::where('status', 'active')->orderBy('first_name')->get();
        $agents       = Agent::where('status', 'active')->orderBy('first_name')->get();
        $reservations = Reservation::with(['property', 'client'])->whereIn('status', ['pending', 'confirmed'])->get();

        $selectedClient = $request->filled('client_id') ? Client::find($request->client_id) : null;

        return view('follow-ups.create', compact('clients', 'agents', 'reservations', 'agentRecord', 'selectedClient'));
    }

    public function store(Request $request)
    {
        $user        = auth()->user();
        $agentRecord = Agent::where('user_id', $user->id)->first();

        $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'agent_id'       => 'nullable|exists:agents,id',
            'reservation_id' => 'nullable|exists:reservations,id',
            'follow_up_date' => 'required|date',
            'follow_up_time' => 'nullable',
            'type'           => 'required|in:call,email,site_visit,meeting',
            'notes'          => 'nullable|string|max:1000',
        ]);

        // If agent, force their own agent_id
        $agentId = $user->isAdmin() ? $request->agent_id : ($agentRecord?->id);

        $schedule = FollowUpSchedule::create([
            'client_id'      => $request->client_id,
            'agent_id'       => $agentId,
            'reservation_id' => $request->reservation_id,
            'follow_up_date' => $request->follow_up_date,
            'follow_up_time' => $request->follow_up_time,
            'type'           => $request->type,
            'notes'          => $request->notes,
            'status'         => 'pending',
        ]);

        // Notify the assigned agent (if admin created it for an agent)
        if ($user->isAdmin() && $agentId) {
            $agentUser = Agent::find($agentId)?->user;
            if ($agentUser) {
                EstateNotification::create([
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $agentUser->id,
                    'type'            => 'follow_up_scheduled',
                    'title'           => 'New Follow-Up Scheduled',
                    'message'         => 'A follow-up has been scheduled for ' . ($schedule->client->full_name ?? 'a client') . ' on ' . $schedule->follow_up_date->format('M d, Y') . '.',
                    'data'            => json_encode(['follow_up_id' => $schedule->id]),
                ]);
            }
        }

        // Notify client
        $clientUser = User::find($schedule->client->user_id ?? null);
        if ($clientUser) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'follow_up_scheduled',
                'title'           => 'Follow-Up Scheduled',
                'message'         => 'A ' . ucfirst(str_replace('_', ' ', $schedule->type)) . ' follow-up has been scheduled for you on ' . $schedule->follow_up_date->format('M d, Y') . '.',
                'data'            => json_encode(['follow_up_id' => $schedule->id]),
            ]);
        }

        return redirect()->route('follow-ups.index')->with('success', 'Follow-up scheduled successfully.');
    }

    public function markDone(FollowUpSchedule $followUp)
    {
        $this->authorizeAccess($followUp);
        $followUp->update(['status' => 'done', 'completed_at' => now()]);
        return back()->with('success', 'Follow-up marked as done.');
    }

    public function cancel(FollowUpSchedule $followUp)
    {
        $this->authorizeAccess($followUp);
        $followUp->update(['status' => 'cancelled']);
        return back()->with('success', 'Follow-up cancelled.');
    }

    public function destroy(FollowUpSchedule $followUp)
    {
        $this->authorizeAccess($followUp);
        $followUp->delete();
        return back()->with('success', 'Follow-up deleted.');
    }

    // Client view — read only
    public function clientIndex()
    {
        $user         = auth()->user();
        $clientRecord = Client::where('user_id', $user->id)->first();

        if (!$clientRecord) {
            return redirect()->route('home')->with('error', 'Client profile not found.');
        }

        $schedules = FollowUpSchedule::with(['agent', 'reservation.property'])
            ->where('client_id', $clientRecord->id)
            ->orderBy('follow_up_date')
            ->paginate(10);

        return view('follow-ups.client', compact('schedules', 'clientRecord'));
    }

    private function authorizeAccess(FollowUpSchedule $followUp)
    {
        $user        = auth()->user();
        $agentRecord = Agent::where('user_id', $user->id)->first();

        if (!$user->isAdmin() && $agentRecord && $followUp->agent_id !== $agentRecord->id) {
            abort(403);
        }
    }
}
