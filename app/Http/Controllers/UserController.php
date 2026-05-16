<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\EstateNotification;
use App\Mail\AccountApproved;
use App\Mail\AccountRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $totalUsers    = User::count();
        $activeUsers   = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();

        return view('admin.users', compact('users', 'totalUsers', 'activeUsers', 'inactiveUsers'));
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $wasInactive = !$user->is_active;
        $user->update(['is_active' => !$user->is_active]);

        // When activating a client account
        if ($wasInactive && $user->is_active && $user->role === 'client') {
            $client = Client::where('user_id', $user->id)->first();

            // Auto-create pending reservation if client registered with a property intent
            if ($client && $client->interested_property_id) {
                $alreadyExists = Reservation::where('client_id', $client->id)
                    ->where('property_id', $client->interested_property_id)
                    ->exists();

                if (!$alreadyExists) {
                    Reservation::create([
                        'property_id'      => $client->interested_property_id,
                        'client_id'        => $client->id,
                        'reservation_date' => now()->toDateString(),
                        'status'           => 'pending',
                        'notes'            => $client->purchase_notes ?? 'Auto-created from registration intent.',
                    ]);

                    Log::info("Auto-reservation created for client #{$client->id} on property #{$client->interested_property_id}");

                    // Notify the client inside the platform
                    EstateNotification::create([
                        'notifiable_type' => User::class,
                        'notifiable_id'   => $user->id,
                        'type'            => 'reservation_created',
                        'data'            => [
                            'title'   => 'Reservation Created',
                            'message' => 'Your account has been approved and a reservation has been created for your selected property. Please log in to view it.',
                        ],
                        'priority' => 'high',
                        'is_read'  => false,
                    ]);
                }
            }

            // Send approval email
            try {
                Mail::to($user->email)->send(new AccountApproved($user, $client?->interested_property_id ? true : false));
                Log::info("Approval email sent to {$user->email} (User ID: {$user->id})");
            } catch (\Exception $e) {
                Log::error("Failed to send approval email to {$user->email}: " . $e->getMessage());
            }
        }

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User \"{$user->name}\" has been {$status}.");
    }

    public function rejectUser(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot reject your own account.');
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        try {
            Mail::to($user->email)->send(new AccountRejected($user, $request->reason));
            Log::info("Rejection email sent to {$user->email} (User ID: {$user->id})");
        } catch (\Exception $e) {
            Log::error("Failed to send rejection email to {$user->email}: " . $e->getMessage());
        }

        $user->delete();

        return back()->with('success', "Registration for \"{$user->name}\" has been rejected and the user has been notified.");
    }

    public function updateRole(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $request->validate(['role' => 'required|in:admin,agent,finance,staff,client']);

        $user->update(['role' => $request->role]);

        return back()->with('success', "Role updated to \"{$request->role}\" for {$user->name}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', "User \"{$user->name}\" has been deleted.");
    }

    public function create()
    {
        return view('admin.users-create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'role'     => 'required|in:admin,agent,finance,staff,client',
            'password' => 'required|string|min:8|confirmed',
        ];

        if ($request->role === 'agent') {
            $rules['agent_phone']     = 'required|string|max:20';
            $rules['license_number']  = 'nullable|string|max:100';
            $rules['commission_rate'] = 'nullable|numeric|min:0|max:100';
            $rules['agent_address']   = 'nullable|string';
            $rules['agent_status']    = 'required|in:active,inactive,suspended';
        }

        $request->validate($rules);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'is_active' => true,
        ]);

        // Auto-create agent profile
        if ($request->role === 'agent') {
            $nameParts = explode(' ', $request->name, 2);
            Agent::create([
                'user_id'         => $user->id,
                'first_name'      => $nameParts[0],
                'last_name'       => $nameParts[1] ?? '',
                'email'           => $request->email,
                'phone'           => $request->agent_phone,
                'license_number'  => $request->license_number,
                'commission_rate' => $request->commission_rate ?? 0,
                'address'         => $request->agent_address,
                'status'          => $request->agent_status,
            ]);
        }

        return redirect()->route('admin.users')->with('success', "User \"{$request->name}\" created successfully.");
    }
}
