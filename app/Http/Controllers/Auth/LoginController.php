<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use App\Models\AuditLog;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact administrator.'],
            ]);
        }

        Auth::login($user);

        AuditLog::log('login', $user, "User {$user->name} logged in.");

        // If there's a redirect parameter, use it (e.g. from public property page)
        if ($request->filled('redirect')) {
            return redirect($request->redirect);
        }

        // Redirect each role to their own dashboard
        return match($user->role) {
            'admin'       => redirect()->route('admin.dashboard'),
            'agent'       => redirect()->route('agent.dashboard'),
            'finance'     => redirect()->route('finance.dashboard'),
            'staff'       => redirect()->route('contractor.dashboard'),
            'client'      => redirect()->route('home'),
            default       => redirect()->route('dashboard'),
        };
    }

    public function logout(Request $request)
    {
        AuditLog::log('logout', null, 'User logged out.');

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
