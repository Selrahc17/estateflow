<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->role;

        return view('dashboard', compact('user', 'role'));
    }

    public function admin()
    {
        return view('admin.dashboard');
    }

    public function users()
    {
        return view('admin.users');
    }

    public function properties()
    {
        return view('admin.properties');
    }

    public function projects()
    {
        return view('admin.projects');
    }

    public function agent()
    {
        return view('agent.dashboard');
    }

    public function agentProperties()
    {
        return view('agent.properties');
    }

    public function agentReservations()
    {
        return view('agent.reservations');
    }

    public function contractor()
    {
        return view('contractor.dashboard');
    }

    public function contractorProjects()
    {
        return view('contractor.projects');
    }

    public function contractorTasks()
    {
        return view('contractor.tasks');
    }

    public function client()
    {
        return view('client.dashboard');
    }

    public function clientProperties()
    {
        return view('client.properties');
    }

    public function clientReservations()
    {
        return view('client.reservations');
    }

    public function clientProjects()
    {
        return view('client.projects');
    }
}
