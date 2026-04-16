<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DashboardController;

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// Profile Routes
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

// Dashboard Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Role-based routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin', [DashboardController::class, 'admin'])->name('admin.dashboard');
        Route::get('/admin/users', [DashboardController::class, 'users'])->name('admin.users');
        Route::get('/admin/properties', [DashboardController::class, 'properties'])->name('admin.properties');
        Route::get('/admin/projects', [DashboardController::class, 'projects'])->name('admin.projects');
    });

    Route::middleware(['role:agent'])->group(function () {
        Route::get('/agent', [DashboardController::class, 'agent'])->name('agent.dashboard');
        Route::get('/agent/properties', [DashboardController::class, 'agentProperties'])->name('agent.properties');
        Route::get('/agent/reservations', [DashboardController::class, 'agentReservations'])->name('agent.reservations');
    });

    Route::middleware(['role:contractor'])->group(function () {
        Route::get('/contractor', [DashboardController::class, 'contractor'])->name('contractor.dashboard');
        Route::get('/contractor/projects', [DashboardController::class, 'contractorProjects'])->name('contractor.projects');
        Route::get('/contractor/tasks', [DashboardController::class, 'contractorTasks'])->name('contractor.tasks');
    });

    Route::middleware(['role:client'])->group(function () {
        Route::get('/client', [DashboardController::class, 'client'])->name('client.dashboard');
        Route::get('/client/properties', [DashboardController::class, 'clientProperties'])->name('client.properties');
        Route::get('/client/reservations', [DashboardController::class, 'clientReservations'])->name('client.reservations');
        Route::get('/client/projects', [DashboardController::class, 'clientProjects'])->name('client.projects');
    });
});

// Test route to verify middleware
Route::get('/test-role', function () {
    return 'Role middleware is working!';
})->middleware('role:admin');

// Default route
Route::get('/', function () {
    return view('welcome');
});

// Property Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('properties', PropertyController::class);
    Route::get('/properties/search', [PropertyController::class, 'search'])->name('properties.search');
});

// Property Type Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('property-types', PropertyTypeController::class);
});
