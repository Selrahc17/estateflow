<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use App\Models\EstateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'phone'      => 'required|string|max:20',
            'password'   => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
            'role'       => 'required|in:client',
            'terms'      => 'accepted',
        ], [
            'password.regex'        => 'Password must contain at least one uppercase letter, one number, and one special character (@$!%*#?&).',
            'role.in'               => 'Only client accounts can self-register.',
            'terms.accepted'        => 'You must agree to the Terms & Conditions.',
            'first_name.required'   => 'First name is required.',
            'last_name.required'    => 'Last name is required.',
            'phone.required'        => 'Phone number is required.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create user — inactive by default, needs admin approval
        $user = User::create([
            'name'      => $request->first_name . ' ' . $request->last_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'client',
            'is_active' => false,
        ]);

        // #4 Auto-link client profile
        Client::create([
            'user_id'    => $user->id,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'status'     => 'active',
        ]);

        // #6 Notify all admins about new registration
        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        foreach ($admins as $admin) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'type'            => 'new_registration',
                'data'            => [
                    'message' => "New client registered: {$user->name} ({$user->email}). Please review and activate their account.",
                    'title'   => 'new_registration',
                ],
                'priority'        => 'normal',
                'is_read'         => false,
            ]);
        }

        // Don't log them in — they need admin approval first
        return redirect()->route('login')->with('status',
            'Registration successful! Your account is pending admin approval. You will be notified once activated.'
        );
    }
}
