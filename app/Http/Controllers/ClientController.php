<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with('user');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $clients = $query->latest()->paginate(15)->withQueryString();

        $totalClients    = Client::count();
        $activeClients   = Client::where('status', 'active')->count();
        $inactiveClients = Client::where('status', 'inactive')->count();

        return view('clients.index', compact('clients', 'totalClients', 'activeClients', 'inactiveClients'));
    }

    public function create()
    {
        $users = User::where('role', 'client')->where('is_active', true)->get();
        return view('clients.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'email'      => 'nullable|email|max:255',
            'phone_alt'  => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'id_type'    => 'nullable|string|max:100',
            'id_number'  => 'nullable|string|max:100',
            'id_expiry'  => 'nullable|date',
            'status'     => 'required|in:active,inactive,blacklisted',
            'notes'      => 'nullable|string',
            'user_id'    => 'nullable|exists:users,id',
        ]);

        Client::create($request->all());

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        $client->load(['reservations.property', 'payments']);
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $users = User::where('role', 'client')->where('is_active', true)->get();
        return view('clients.edit', compact('client', 'users'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'email'      => 'nullable|email|max:255',
            'phone_alt'  => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'id_type'    => 'nullable|string|max:100',
            'id_number'  => 'nullable|string|max:100',
            'id_expiry'  => 'nullable|date',
            'status'     => 'required|in:active,inactive,blacklisted',
            'notes'      => 'nullable|string',
            'user_id'    => 'nullable|exists:users,id',
        ]);

        $client->update($request->all());

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return back()->with('success', "Client \"{$client->full_name}\" deleted successfully.");
    }
}
