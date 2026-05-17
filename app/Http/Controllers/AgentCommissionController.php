<?php

namespace App\Http\Controllers;

use App\Models\AgentCommission;
use App\Models\Reservation;
use App\Models\Agent;
use Illuminate\Http\Request;

class AgentCommissionController extends Controller
{
    public function index(Request $request)
    {
        $query = AgentCommission::with(['agent', 'reservation.property', 'reservation.client']);

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('agent_id')) $query->where('agent_id', $request->agent_id);

        $commissions     = $query->latest()->paginate(15)->withQueryString();
        $totalPending    = AgentCommission::where('status', 'pending')->sum('commission_amount');
        $totalApproved   = AgentCommission::where('status', 'approved')->sum('commission_amount');
        $totalPaid       = AgentCommission::where('status', 'paid')->sum('commission_amount');
        $agents          = Agent::where('status', 'active')->get();

        return view('commissions.index', compact(
            'commissions', 'totalPending', 'totalApproved', 'totalPaid', 'agents'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reservation_id'  => 'required|exists:reservations,id',
            'agent_id'        => 'required|exists:agents,id',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'notes'           => 'nullable|string|max:500',
        ]);

        $reservation = Reservation::with('property')->findOrFail($request->reservation_id);

        if ($reservation->commission) {
            return back()->with('error', 'Commission already exists for this reservation.');
        }

        $price  = (float) $reservation->property->price;
        $rate   = (float) $request->commission_rate;
        $amount = round($price * $rate / 100, 2);

        AgentCommission::create([
            'reservation_id'    => $reservation->id,
            'agent_id'          => $request->agent_id,
            'property_price'    => $price,
            'commission_rate'   => $rate,
            'commission_amount' => $amount,
            'status'            => 'pending',
            'notes'             => $request->notes,
            'created_by'        => auth()->id(),
        ]);

        return back()->with('success', 'Commission of ₱' . number_format($amount, 2) . ' created.');
    }

    public function approve(AgentCommission $commission)
    {
        if ($commission->status !== 'pending') {
            return back()->with('error', 'Only pending commissions can be approved.');
        }
        $commission->update(['status' => 'approved', 'approved_at' => now()]);
        return back()->with('success', 'Commission approved.');
    }

    public function markPaid(Request $request, AgentCommission $commission)
    {
        $request->validate([
            'or_number' => 'required|string|max:100',
        ]);

        if ($commission->status !== 'approved') {
            return back()->with('error', 'Only approved commissions can be marked as paid.');
        }

        $commission->update([
            'status'    => 'paid',
            'paid_at'   => now(),
            'or_number' => $request->or_number,
        ]);

        // Notify agent
        $agentUser = $commission->agent?->user;
        if ($agentUser) {
            \App\Models\EstateNotification::create([
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id'   => $agentUser->id,
                'type'            => 'commission_paid',
                'data'            => [
                    'title'   => 'Commission Paid — OR# ' . $request->or_number,
                    'message' => '₱' . number_format($commission->commission_amount, 2) . ' commission for ' . ($commission->reservation->property->title ?? 'a property') . ' has been released. OR# ' . $request->or_number,
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', 'Commission marked as paid. OR# ' . $request->or_number);
    }

    public function cancel(AgentCommission $commission)
    {
        if ($commission->status === 'paid') {
            return back()->with('error', 'Paid commissions cannot be cancelled.');
        }
        $commission->update(['status' => 'cancelled']);
        return back()->with('success', 'Commission cancelled.');
    }
}
