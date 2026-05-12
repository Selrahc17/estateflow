<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use App\Models\Client;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Document;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->role;

        // Redirect each role directly to their own dashboard
        return match($role) {
            'admin'      => redirect()->route('admin.dashboard'),
            'agent'      => redirect()->route('agent.dashboard'),
            'finance'    => redirect()->route('finance.dashboard'),
            'staff'      => redirect()->route('contractor.dashboard'),
            'client'     => redirect()->route('home'),
            default      => view('dashboard', [
                'user'              => $user,
                'role'              => $role,
                'totalProperties'   => \App\Models\Property::count(),
                'totalProjects'     => \App\Models\Project::count(),
                'totalReservations' => \App\Models\Reservation::count(),
                'totalClients'      => \App\Models\Client::count(),
            ]),
        };
    }

    public function admin()
    {
        $totalUsers       = User::count();
        $pendingUsers     = User::where('is_active', false)->count();
        $totalProperties  = \App\Models\Property::count();
        $activeProjects   = \App\Models\Project::where('status', 'in_progress')->count();
        $totalReservations = \App\Models\Reservation::count();
        $totalRevenue     = \App\Models\Payment::where('status', 'completed')->sum('amount');
        $recentUsers      = User::where('is_active', false)->latest()->take(5)->get(); // pending approvals first
        $propertiesByStatus = \App\Models\Property::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        $recentReservations = \App\Models\Reservation::with(['property', 'client'])
            ->latest()->take(5)->get();
        $recentPayments = \App\Models\Payment::with('client')
            ->where('status', 'completed')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'pendingUsers', 'totalProperties', 'activeProjects',
            'totalReservations', 'totalRevenue', 'recentUsers',
            'propertiesByStatus', 'recentReservations', 'recentPayments'
        ));
    }

    public function properties()
    {
        return redirect()->route('properties.index');
    }

    public function projects()
    {
        return redirect()->route('projects.index');
    }

    public function users()
    {
        return redirect()->route('admin.users');
    }

    public function agent()
    {
        $user = Auth::user();
        $agentRecord = \App\Models\Agent::where('user_id', $user->id)->first();

        $myReservations   = 0;
        $pendingCount     = 0;
        $confirmedCount   = 0;
        $myClients        = 0;
        $myCommission     = 0;
        $recentReservations = collect();

        if ($agentRecord) {
            $myReservations = \App\Models\Reservation::where('agent_id', $agentRecord->id)->count();
            $pendingCount   = \App\Models\Reservation::where('agent_id', $agentRecord->id)->where('status', 'pending')->count();
            $confirmedCount = \App\Models\Reservation::where('agent_id', $agentRecord->id)->where('status', 'confirmed')->count();
            $myClients      = \App\Models\Reservation::where('agent_id', $agentRecord->id)->distinct('client_id')->count('client_id');
            $myCommission   = \App\Models\Payment::where('agent_id', $agentRecord->id)->where('status', 'completed')->sum('amount');
            $recentReservations = \App\Models\Reservation::with(['property', 'client'])
                ->where('agent_id', $agentRecord->id)
                ->latest()->take(5)->get();
        }

        return view('agent.dashboard', compact(
            'agentRecord', 'myReservations', 'pendingCount',
            'confirmedCount', 'myClients', 'myCommission', 'recentReservations'
        ));
    }

    public function agentProperties()
    {
        return redirect()->route('properties.index');
    }

    public function agentReservations()
    {
        $user = Auth::user();
        $agentRecord = \App\Models\Agent::where('user_id', $user->id)->first();

        if ($user->isAdmin()) {
            $reservations   = \App\Models\Reservation::with(['property', 'client', 'payments', 'agent'])
                ->latest()->paginate(15);
            $pendingCount   = \App\Models\Reservation::where('status', 'pending')->count();
            $confirmedCount = \App\Models\Reservation::where('status', 'confirmed')->count();
        } else {
            $reservations = $agentRecord
                ? \App\Models\Reservation::with(['property', 'client', 'payments'])
                    ->where('agent_id', $agentRecord->id)
                    ->latest()->paginate(15)
                : collect();
            $pendingCount   = $agentRecord ? \App\Models\Reservation::where('agent_id', $agentRecord->id)->where('status','pending')->count() : 0;
            $confirmedCount = $agentRecord ? \App\Models\Reservation::where('agent_id', $agentRecord->id)->where('status','confirmed')->count() : 0;
        }

        return view('agent.reservations', compact('reservations', 'agentRecord', 'pendingCount', 'confirmedCount'));
    }

    public function contractor()
    {
        $user = Auth::user();
        $contractorRecord = \App\Models\Staff::where('user_id', $user->id)->first();

        $myProjects     = 0;
        $activeProjects = 0;
        $myTasks        = 0;
        $pendingTasks   = 0;
        $recentProjects = collect();
        $urgentTasks    = collect();

        if ($contractorRecord) {
            $myProjects     = \App\Models\Project::where('staff_id', $contractorRecord->id)->count();
            $activeProjects = \App\Models\Project::where('staff_id', $contractorRecord->id)->where('status','in_progress')->count();
            $myTasks        = \App\Models\Task::whereHas('project', fn($q) => $q->where('staff_id', $contractorRecord->id))->count();
            $pendingTasks   = \App\Models\Task::whereHas('project', fn($q) => $q->where('staff_id', $contractorRecord->id))->where('status','pending')->count();
            $recentProjects = \App\Models\Project::where('staff_id', $contractorRecord->id)->latest()->take(5)->get();
            $urgentTasks    = \App\Models\Task::whereHas('project', fn($q) => $q->where('staff_id', $contractorRecord->id))
                ->whereIn('priority', ['high','urgent'])
                ->where('status','!=','completed')
                ->latest()->take(5)->get();
        }

        return view('contractor.dashboard', compact(
            'contractorRecord', 'myProjects', 'activeProjects',
            'myTasks', 'pendingTasks', 'recentProjects', 'urgentTasks'
        ));
    }

    public function contractorProjects()
    {
        $user = Auth::user();
        $contractorRecord = \App\Models\Staff::where('user_id', $user->id)->first();

        $projects = $user->isAdmin()
            ? \App\Models\Project::with(['property', 'client', 'staff'])->latest()->paginate(15)
            : ($contractorRecord
                ? \App\Models\Project::with(['property', 'client'])
                    ->where('staff_id', $contractorRecord->id)
                    ->latest()->paginate(15)
                : collect());

        return view('contractor.projects', compact('projects', 'contractorRecord'));
    }

    public function contractorTasks()
    {
        $user = Auth::user();
        $contractorRecord = \App\Models\Staff::where('user_id', $user->id)->first();

        if ($user->isAdmin()) {
            $tasks           = \App\Models\Task::with(['project', 'assignedTo'])->latest()->paginate(15);
            $pendingTasks    = \App\Models\Task::where('status', 'pending')->count();
            $inProgressTasks = \App\Models\Task::where('status', 'in_progress')->count();
        } else {
            $tasks = $contractorRecord
                ? \App\Models\Task::with(['project', 'assignedTo'])
                    ->whereHas('project', fn($q) => $q->where('staff_id', $contractorRecord->id))
                    ->latest()->paginate(15)
                : collect();
            $pendingTasks    = $contractorRecord
                ? \App\Models\Task::whereHas('project', fn($q) => $q->where('staff_id', $contractorRecord->id))->where('status','pending')->count()
                : 0;
            $inProgressTasks = $contractorRecord
                ? \App\Models\Task::whereHas('project', fn($q) => $q->where('staff_id', $contractorRecord->id))->where('status','in_progress')->count()
                : 0;
        }

        return view('contractor.tasks', compact('tasks', 'contractorRecord', 'pendingTasks', 'inProgressTasks'));
    }

    // ====================================
    // CLIENT METHODS — now serve real views
    // ====================================

    public function client()
    {
        $user = Auth::user();
        $clientRecord = Client::where('user_id', $user->id)->first();

        $totalProperties = Property::where('is_active', true)->count();
        $myReservations = 0;
        $recentReservations = collect();
        $totalPayments = 0;

        if ($clientRecord) {
            $myReservations = Reservation::where('client_id', $clientRecord->id)->count();
            $recentReservations = Reservation::with(['property', 'agent'])
                ->where('client_id', $clientRecord->id)
                ->latest()->take(5)->get();
            $totalPayments = Payment::where('client_id', $clientRecord->id)
                ->where('status', 'completed')->sum('amount');
        }

        return view('client.dashboard', compact(
            'clientRecord', 'totalProperties', 'myReservations', 'recentReservations', 'totalPayments'
        ));
    }

    public function requestPagibig(\App\Models\Reservation $reservation)
    {
        $user = Auth::user();
        $clientRecord = Client::where('user_id', $user->id)->first();

        // Guard: must be their own reservation
        if (!$clientRecord || $reservation->client_id !== $clientRecord->id) {
            abort(403, 'This reservation does not belong to you.');
        }

        // Guard: reservation must be confirmed
        if ($reservation->status !== 'confirmed') {
            return back()->with('error', 'You can only apply for Pag-IBIG on a confirmed reservation.');
        }

        // Guard: must not have already applied
        if ($reservation->pagibig_status !== 'not_applied') {
            return back()->with('error', 'You have already submitted a Pag-IBIG application for this reservation.');
        }
        // Update status
        $reservation->update(['pagibig_status' => 'applied']);

        // Notify admin and assigned agent
        $notifyUsers = \App\Models\User::where('role', 'admin')->where('is_active', true)->get();
        if ($reservation->agent && $reservation->agent->user_id) {
            $agentUser = \App\Models\User::find($reservation->agent->user_id);
            if ($agentUser) $notifyUsers = $notifyUsers->push($agentUser);
        }

        foreach ($notifyUsers as $notifyUser) {
            \App\Models\EstateNotification::create([
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id'   => $notifyUser->id,
                'type'            => 'pagibig_request',
                'title'           => 'Pag-IBIG Application — ' . $clientRecord->full_name,
                'message'         => $clientRecord->full_name . ' has applied for a Pag-IBIG loan for ' . ($reservation->property->title ?? 'a property') . '.',
                'data'            => json_encode(['reservation_id' => $reservation->id]),
            ]);
        }

        return back()->with('success', 'Your Pag-IBIG application has been submitted. Your agent will update you on the progress.');
    }

    public function clientProperties()
    {
        return redirect()->route('home.browse');
    }

    public function clientReservations()
    {
        $user = Auth::user();
        $clientRecord = Client::where('user_id', $user->id)->first();

        $reservations = collect();
        $totalCount = $pendingCount = $confirmedCount = 0;

        if ($clientRecord) {
            $query = Reservation::with(['property', 'agent', 'payments'])
                ->where('client_id', $clientRecord->id);

            if (request('status')) {
                $query->where('status', request('status'));
            }

            $reservations   = $query->latest()->paginate(15);
            $totalCount     = Reservation::where('client_id', $clientRecord->id)->count();
            $pendingCount   = Reservation::where('client_id', $clientRecord->id)->where('status', 'pending')->count();
            $confirmedCount = Reservation::where('client_id', $clientRecord->id)->where('status', 'confirmed')->count();
        }

        return view('client.reservations', compact('reservations', 'clientRecord', 'totalCount', 'pendingCount', 'confirmedCount'));
    }

    public function clientPayments()
    {
        $user = Auth::user();
        $clientRecord = Client::where('user_id', $user->id)->first();

        $payments = collect();
        if ($clientRecord) {
            $payments = Payment::with(['reservation.property'])
                ->where('client_id', $clientRecord->id)
                ->latest()->paginate(15);
        }

        return view('client.payments', compact('payments', 'clientRecord'));
    }

    public function clientProjects()
    {
        return redirect()->route('home');
    }

    public function clientDocuments()
    {
        $user = Auth::user();
        $clientRecord = Client::where('user_id', $user->id)->first();

        $documents    = collect();
        $reservations = collect();

        if ($clientRecord) {
            $reservationIds = Reservation::where('client_id', $clientRecord->id)->pluck('id');
            $documents = Document::where(function ($q) use ($clientRecord, $reservationIds) {
                $q->where(function ($q2) use ($clientRecord) {
                    $q2->where('documentable_type', Client::class)
                       ->where('documentable_id', $clientRecord->id);
                })->orWhere(function ($q2) use ($reservationIds) {
                    $q2->where('documentable_type', Reservation::class)
                       ->whereIn('documentable_id', $reservationIds);
                });
            })->latest()->paginate(15);

            $reservations = Reservation::with('property')
                ->where('client_id', $clientRecord->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->get();
        }

        return view('client.documents', compact('documents', 'clientRecord', 'reservations'));
    }
}