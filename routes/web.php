<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ProgressLogController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AIPredictionController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\FinanceController;

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
        Route::get('/admin/properties', [DashboardController::class, 'properties'])->name('admin.properties');
        Route::get('/admin/projects', [DashboardController::class, 'projects'])->name('admin.projects');

        // User Management
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
        Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::patch('/admin/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
        Route::patch('/admin/users/{user}/role', [UserController::class, 'updateRole'])->name('admin.users.update-role');
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    });

    Route::middleware(['role:finance'])->group(function () {
        Route::get('/finance', [FinanceController::class, 'dashboard'])->name('finance.dashboard');
        Route::get('/finance/payments', [FinanceController::class, 'payments'])->name('finance.payments');
        Route::get('/finance/payments/create', [FinanceController::class, 'createPayment'])->name('finance.payments.create');
        Route::post('/finance/payments', [FinanceController::class, 'storePayment'])->name('finance.payments.store');
        Route::get('/finance/pagibig', [FinanceController::class, 'pagibig'])->name('finance.pagibig');
        Route::get('/finance/pagibig/{reservation}/record', [FinanceController::class, 'recordPagibig'])->name('finance.pagibig.record');
        Route::post('/finance/pagibig/{reservation}/record', [FinanceController::class, 'storePagibig'])->name('finance.pagibig.store');
        Route::get('/finance/export/csv', [FinanceController::class, 'exportCsv'])->name('finance.export.csv');
        Route::get('/finance/export/pdf', [FinanceController::class, 'exportPdf'])->name('finance.export.pdf');
    });

    Route::middleware(['role:agent'])->group(function () {
        Route::get('/agent', [DashboardController::class, 'agent'])->name('agent.dashboard');
        Route::get('/agent/properties', [DashboardController::class, 'agentProperties'])->name('agent.properties');
        Route::get('/agent/reservations', [DashboardController::class, 'agentReservations'])->name('agent.reservations');
    });

    Route::middleware(['role:staff'])->group(function () {
        Route::get('/contractor', [DashboardController::class, 'contractor'])->name('contractor.dashboard');
        Route::get('/contractor/projects', [DashboardController::class, 'contractorProjects'])->name('contractor.projects');
        Route::get('/contractor/tasks', [DashboardController::class, 'contractorTasks'])->name('contractor.tasks');
    });

    Route::middleware(['role:client'])->group(function () {
        Route::get('/client/reservations', [DashboardController::class, 'clientReservations'])->name('client.reservations');
        Route::get('/client/payments', [DashboardController::class, 'clientPayments'])->name('client.payments');
        Route::get('/client/documents', [DashboardController::class, 'clientDocuments'])->name('client.documents');
        Route::post('/client/reservations/{reservation}/pagibig-request', [DashboardController::class, 'requestPagibig'])->name('client.pagibig.request');
    });
});

// Test route to verify middleware
Route::get('/test-role', function () {
    return 'Role middleware is working!';
})->middleware('role:admin');

// Public Routes (no auth required)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/browse', [HomeController::class, 'browse'])->name('home.browse');
Route::get('/property/{property}', [HomeController::class, 'property'])->name('home.property');
Route::post('/inquiry', [HomeController::class, 'inquiry'])->name('home.inquiry');

// Property Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('properties', PropertyController::class);
    Route::get('/properties/search', [PropertyController::class, 'search'])->name('properties.search');
    Route::patch('/properties/{property}/toggle-featured', [PropertyController::class, 'toggleFeatured'])->name('properties.toggle-featured');
});

// Property Type Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('property-types', PropertyTypeController::class);
});

// Client Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('clients', ClientController::class);
});

// Agent Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('agents', AgentController::class);
});

// Reservation Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('reservations', ReservationController::class);
    Route::patch('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus'])->name('reservations.update-status');
    Route::patch('/reservations/{reservation}/pagibig', [ReservationController::class, 'updatePagibig'])->name('reservations.update-pagibig');
});

// Payment Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('payments', PaymentController::class)->middleware('role:admin');
    Route::get('/payments/export/csv', [PaymentController::class, 'exportCsv'])->name('payments.export.csv')->middleware('role:admin');
    Route::get('/payments/export/pdf', [PaymentController::class, 'exportPdf'])->name('payments.export.pdf')->middleware('role:admin');
});

// Contractor/Staff Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('contractors', StaffController::class);
});

// Project Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('projects', ProjectController::class);
});

// Task Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('tasks', TaskController::class);
});

// Milestone Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('milestones', MilestoneController::class);
});

// Budget Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('budgets', BudgetController::class);
});

// Progress Log Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('progress-logs', ProgressLogController::class);
});

// Resource Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('resources', ResourceController::class);
});

// Document Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('documents', DocumentController::class);
    Route::patch('/documents/{document}/verify', [DocumentController::class, 'verify'])->name('documents.verify');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
});

// Notification Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Audit Log Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    Route::delete('/audit-logs/{auditLog}', [AuditLogController::class, 'destroy'])->name('audit-logs.destroy');
    Route::post('/audit-logs/clear', [AuditLogController::class, 'clear'])->name('audit-logs.clear');
});

// AI Prediction Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/ai-predictions', [AIPredictionController::class, 'index'])->name('ai-predictions.index');
    Route::get('/ai-predictions/create', [AIPredictionController::class, 'create'])->name('ai-predictions.create');
    Route::post('/ai-predictions', [AIPredictionController::class, 'store'])->name('ai-predictions.store');
    Route::get('/ai-predictions/{aiPrediction}', [AIPredictionController::class, 'show'])->name('ai-predictions.show');
    Route::delete('/ai-predictions/{aiPrediction}', [AIPredictionController::class, 'destroy'])->name('ai-predictions.destroy');
});

// Message Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/new', [MessageController::class, 'create'])->name('messages.create');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [MessageController::class, 'send'])->name('messages.send');
    Route::get('/messages/{user}/poll', [MessageController::class, 'poll'])->name('messages.poll');
    Route::post('/messages/{user}/typing', [MessageController::class, 'typing'])->name('messages.typing');
    Route::get('/messages/{user}/is-typing', [MessageController::class, 'isTyping'])->name('messages.is-typing');
});
