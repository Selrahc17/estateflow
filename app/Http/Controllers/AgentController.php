<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $query = Agent::with('user');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('license_number', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $agents        = $query->latest()->paginate(15)->withQueryString();
        $totalAgents   = Agent::count();
        $activeAgents  = Agent::where('status', 'active')->count();
        $suspendedAgents = Agent::where('status', 'suspended')->count();

        return view('agents.index', compact('agents', 'totalAgents', 'activeAgents', 'suspendedAgents'));
    }

    public function create()
    {
        $users = User::where('role', 'agent')->where('is_active', true)->get();
        return view('agents.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|email|unique:agents,email',
            'phone'           => 'required|string|max:20',
            'license_number'  => 'nullable|string|max:100',
            'address'         => 'nullable|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status'          => 'required|in:active,inactive,suspended',
            'notes'           => 'nullable|string',
            'user_id'         => 'nullable|exists:users,id',
        ]);

        Agent::create($request->all());

        return redirect()->route('agents.index')->with('success', 'Agent created successfully.');
    }

    public function show(Agent $agent)
    {
        $agent->load(['reservations.property', 'payments']);
        return view('agents.show', compact('agent'));
    }

    public function edit(Agent $agent)
    {
        $users = User::where('role', 'agent')->where('is_active', true)->get();
        return view('agents.edit', compact('agent', 'users'));
    }

    public function update(Request $request, Agent $agent)
    {
        $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|email|unique:agents,email,' . $agent->id,
            'phone'           => 'required|string|max:20',
            'license_number'  => 'nullable|string|max:100',
            'address'         => 'nullable|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status'          => 'required|in:active,inactive,suspended',
            'notes'           => 'nullable|string',
            'user_id'         => 'nullable|exists:users,id',
        ]);

        $agent->update($request->all());

        return redirect()->route('agents.index')->with('success', 'Agent updated successfully.');
    }

    public function destroy(Agent $agent)
    {
        $agent->delete();
        return back()->with('success', "Agent \"{$agent->full_name}\" deleted successfully.");
    }
}
