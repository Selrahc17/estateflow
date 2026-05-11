@extends('layouts.app')

@section('title', 'Dashboard - EstateFlow')
@section('page-title', 'Welcome back, {{ $user->name }}')
@section('page-subtitle', 'Here\'s what\'s happening today')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-building text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Properties</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalProperties }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-project-diagram text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Projects</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalProjects }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-calendar-check text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Reservations</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalReservations }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-users text-purple-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Clients</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalClients }}</p>
        </div>
    </div>

</div>

<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold text-gray-800 mb-4">Quick Actions</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @if($role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center gap-2 p-4 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition">
                <i class="fas fa-tachometer-alt text-indigo-600 text-2xl"></i>
                <span class="text-sm font-medium text-indigo-700">Admin Panel</span>
            </a>
            <a href="{{ route('admin.users') }}" class="flex flex-col items-center gap-2 p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition">
                <i class="fas fa-users text-blue-600 text-2xl"></i>
                <span class="text-sm font-medium text-blue-700">Manage Users</span>
            </a>
            <a href="{{ route('properties.index') }}" class="flex flex-col items-center gap-2 p-4 bg-green-50 rounded-xl hover:bg-green-100 transition">
                <i class="fas fa-building text-green-600 text-2xl"></i>
                <span class="text-sm font-medium text-green-700">Properties</span>
            </a>
            <a href="{{ route('property-types.index') }}" class="flex flex-col items-center gap-2 p-4 bg-yellow-50 rounded-xl hover:bg-yellow-100 transition">
                <i class="fas fa-tags text-yellow-600 text-2xl"></i>
                <span class="text-sm font-medium text-yellow-700">Property Types</span>
            </a>
        @elseif($role === 'agent')
            <a href="{{ route('agent.dashboard') }}" class="flex flex-col items-center gap-2 p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition">
                <i class="fas fa-tachometer-alt text-blue-600 text-2xl"></i>
                <span class="text-sm font-medium text-blue-700">Agent Panel</span>
            </a>
            <a href="{{ route('properties.index') }}" class="flex flex-col items-center gap-2 p-4 bg-green-50 rounded-xl hover:bg-green-100 transition">
                <i class="fas fa-building text-green-600 text-2xl"></i>
                <span class="text-sm font-medium text-green-700">Properties</span>
            </a>
            <a href="{{ route('agent.reservations') }}" class="flex flex-col items-center gap-2 p-4 bg-yellow-50 rounded-xl hover:bg-yellow-100 transition">
                <i class="fas fa-calendar-check text-yellow-600 text-2xl"></i>
                <span class="text-sm font-medium text-yellow-700">Reservations</span>
            </a>
        @elseif($role === 'contractor')
            <a href="{{ route('contractor.dashboard') }}" class="flex flex-col items-center gap-2 p-4 bg-green-50 rounded-xl hover:bg-green-100 transition">
                <i class="fas fa-hard-hat text-green-600 text-2xl"></i>
                <span class="text-sm font-medium text-green-700">Contractor Panel</span>
            </a>
            <a href="{{ route('contractor.projects') }}" class="flex flex-col items-center gap-2 p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition">
                <i class="fas fa-project-diagram text-blue-600 text-2xl"></i>
                <span class="text-sm font-medium text-blue-700">Projects</span>
            </a>
            <a href="{{ route('contractor.tasks') }}" class="flex flex-col items-center gap-2 p-4 bg-yellow-50 rounded-xl hover:bg-yellow-100 transition">
                <i class="fas fa-tasks text-yellow-600 text-2xl"></i>
                <span class="text-sm font-medium text-yellow-700">Tasks</span>
            </a>
        @elseif($role === 'client')
            <a href="{{ route('client.dashboard') }}" class="flex flex-col items-center gap-2 p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition">
                <i class="fas fa-tachometer-alt text-purple-600 text-2xl"></i>
                <span class="text-sm font-medium text-purple-700">My Dashboard</span>
            </a>
            <a href="{{ route('properties.index') }}" class="flex flex-col items-center gap-2 p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition">
                <i class="fas fa-building text-blue-600 text-2xl"></i>
                <span class="text-sm font-medium text-blue-700">Browse Properties</span>
            </a>
        @endif
        <a href="{{ route('profile.edit') }}" class="flex flex-col items-center gap-2 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
            <i class="fas fa-user-cog text-gray-600 text-2xl"></i>
            <span class="text-sm font-medium text-gray-700">My Profile</span>
        </a>
    </div>
</div>
@endsection
