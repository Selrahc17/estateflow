<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Client;
use App\Models\EstateNotification;
use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user        = auth()->user();
        $agentRecord = Agent::where('user_id', $user->id)->first();

        $query = Lead::with(['assignedAgent', 'interestedProperty', 'convertedClient']);

        if (!$user->isAdmin() && $agentRecord) {
            $query->where('assigned_agent_id', $agentRecord->id);
        }

        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('source'))  $query->where('source', $request->source);
        if ($request->filled('search')) {
            $query->where(fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
            );
        }

        $leads        = $query->latest()->paginate(15)->withQueryString();
        $newCount     = Lead::when(!$user->isAdmin() && $agentRecord, fn($q) => $q->where('assigned_agent_id', $agentRecord->id))->where('status', 'new')->count();
        $qualifiedCount = Lead::when(!$user->isAdmin() && $agentRecord, fn($q) => $q->where('assigned_agent_id', $agentRecord->id))->where('status', 'qualified')->count();
        $convertedCount = Lead::when(!$user->isAdmin() && $agentRecord, fn($q) => $q->where('assigned_agent_id', $agentRecord->id))->where('status', 'converted')->count();
        $agents       = Agent::where('status', 'active')->get();
        $properties   = Property::where('is_active', true)->where('status', 'available')->get();

        return view('leads.index', compact('leads', 'newCount', 'qualifiedCount', 'convertedCount', 'agents', 'properties'));
    }

    public function create()
    {
        $agents     = Agent::where('status', 'active')->get();
        $properties = Property::where('is_active', true)->where('status', 'available')->get();
        return view('leads.create', compact('agents', 'properties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'email'                  => 'nullable|email|max:150',
            'phone'                  => 'nullable|string|max:30',
            'source'                 => 'required|in:website_inquiry,referral,walk_in,social_media,phone_call,other',
            'assigned_agent_id'      => 'nullable|exists:agents,id',
            'interested_property_id' => 'nullable|exists:properties,id',
            'budget_min'             => 'nullable|numeric|min:0',
            'budget_max'             => 'nullable|numeric|min:0',
            'preferred_location'     => 'nullable|string|max:255',
            'notes'                  => 'nullable|string|max:1000',
        ]);

        $lead = Lead::create($request->all() + ['status' => 'new']);

        // Notify assigned agent
        if ($lead->assigned_agent_id) {
            $agentUser = $lead->assignedAgent?->user;
            if ($agentUser) {
                EstateNotification::create([
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $agentUser->id,
                    'type'            => 'lead_assigned',
                    'title'           => 'New Lead Assigned — ' . $lead->name,
                    'message'         => 'A new lead has been assigned to you: ' . $lead->name . ' (' . $lead->source . ').',
                    'data'            => json_encode(['lead_id' => $lead->id]),
                ]);
            }
        }

        return redirect()->route('leads.index')->with('success', 'Lead added successfully.');
    }

    public function edit(Lead $lead)
    {
        $agents     = Agent::where('status', 'active')->get();
        $properties = Property::where('is_active', true)->get();
        $clients    = Client::where('status', 'active')->get();
        return view('leads.edit', compact('lead', 'agents', 'properties', 'clients'));
    }

    public function update(Request $request, Lead $lead)
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'email'                  => 'nullable|email|max:150',
            'phone'                  => 'nullable|string|max:30',
            'source'                 => 'required|in:website_inquiry,referral,walk_in,social_media,phone_call,other',
            'status'                 => 'required|in:new,contacted,qualified,converted,lost',
            'assigned_agent_id'      => 'nullable|exists:agents,id',
            'interested_property_id' => 'nullable|exists:properties,id',
            'converted_client_id'    => 'nullable|exists:clients,id',
            'budget_min'             => 'nullable|numeric|min:0',
            'budget_max'             => 'nullable|numeric|min:0',
            'preferred_location'     => 'nullable|string|max:255',
            'notes'                  => 'nullable|string|max:1000',
        ]);

        $data = $request->all();

        // Auto set last_contacted_at when status changes to contacted
        if ($request->status === 'contacted' && $lead->status !== 'contacted') {
            $data['last_contacted_at'] = now();
        }

        $lead->update($data);

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $request->validate(['status' => 'required|in:new,contacted,qualified,converted,lost']);

        $data = ['status' => $request->status];
        if ($request->status === 'contacted') {
            $data['last_contacted_at'] = now();
        }

        $lead->update($data);
        return back()->with('success', 'Lead status updated to ' . ucfirst($request->status) . '.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return back()->with('success', 'Lead deleted.');
    }
}
