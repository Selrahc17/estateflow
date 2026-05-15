<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Lead;
use App\Models\Reservation;
use App\Models\Payment;

class SalesPipelineController extends Controller
{
    public function index()
    {
        $user        = auth()->user();
        $agentRecord = Agent::where('user_id', $user->id)->first();

        $leadQuery        = Lead::with(['assignedAgent', 'interestedProperty']);
        $reservationQuery = Reservation::with(['client', 'property', 'agent', 'payments']);

        if (!$user->isAdmin() && $agentRecord) {
            $leadQuery->where('assigned_agent_id', $agentRecord->id);
            $reservationQuery->where('agent_id', $agentRecord->id);
        }

        // Stages 1-3: from leads
        $newLeads       = (clone $leadQuery)->where('status', 'new')->latest()->get();
        $contactedLeads = (clone $leadQuery)->where('status', 'contacted')->latest()->get();
        $qualifiedLeads = (clone $leadQuery)->where('status', 'qualified')->latest()->get();

        // Stages 4-6: from reservations
        $reservedDeals  = (clone $reservationQuery)->where('status', 'pending')->latest()->get();
        $activeDeals    = (clone $reservationQuery)->where('status', 'confirmed')->latest()->get();
        $closedDeals    = (clone $reservationQuery)->where('status', 'completed')->latest()->get();

        // Pipeline value (sum of property prices in active stages)
        $pipelineValue = $reservedDeals->sum(fn($r) => $r->property->price ?? 0)
                       + $activeDeals->sum(fn($r) => $r->property->price ?? 0);

        // Conversion rate: leads → reservations
        $totalLeads       = (clone $leadQuery)->count();
        $convertedLeads   = (clone $leadQuery)->where('status', 'converted')->count();
        $conversionRate   = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100) : 0;

        // Revenue closed
        $closedRevenue = $closedDeals->sum(fn($r) => $r->property->price ?? 0);

        return view('pipeline.index', compact(
            'newLeads', 'contactedLeads', 'qualifiedLeads',
            'reservedDeals', 'activeDeals', 'closedDeals',
            'pipelineValue', 'conversionRate', 'closedRevenue',
            'totalLeads', 'convertedLeads'
        ));
    }
}
