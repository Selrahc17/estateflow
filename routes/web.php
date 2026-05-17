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
use App\Http\Controllers\RetentionController;
use App\Http\Controllers\SalesPipelineController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProjectIssueController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\SiteViewingController;

use App\Http\Controllers\AgentCommissionController;

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
        Route::post('/admin/users/{user}/reject', [UserController::class, 'rejectUser'])->name('admin.users.reject');
    });

    // Data Retention (admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/retention', [RetentionController::class, 'index'])->name('retention.index');
        Route::post('/retention/run', [RetentionController::class, 'run'])->name('retention.run');
    });

    Route::middleware(['role:finance'])->group(function () {
        Route::get('/finance', [FinanceController::class, 'dashboard'])->name('finance.dashboard');
        Route::get('/finance/payments', [FinanceController::class, 'payments'])->name('finance.payments');
        Route::get('/finance/payments/create', [FinanceController::class, 'createPayment'])->name('finance.payments.create');
        Route::post('/finance/payments', [FinanceController::class, 'storePayment'])->name('finance.payments.store');
        Route::get('/finance/units/search', [FinanceController::class, 'searchUnits'])->name('finance.units.search');
        Route::get('/finance/pagibig', [FinanceController::class, 'pagibig'])->name('finance.pagibig');
        Route::get('/finance/pagibig/{reservation}/record', [FinanceController::class, 'recordPagibig'])->name('finance.pagibig.record');
        Route::post('/finance/pagibig/{reservation}/record', [FinanceController::class, 'storePagibig'])->name('finance.pagibig.store');
        Route::get('/finance/export/csv', [FinanceController::class, 'exportCsv'])->name('finance.export.csv');
        Route::get('/finance/export/pdf', [FinanceController::class, 'exportPdf'])->name('finance.export.pdf');
        Route::get('/finance/clients/{client}/payments', [FinanceController::class, 'clientPayments'])->name('finance.client.payments');
        Route::get('/finance/clients/{client}/payments/{reservation}', [FinanceController::class, 'reservationPayments'])->name('finance.reservation.payments');
        Route::get('/finance/reports/monthly', [FinanceController::class, 'monthlyReport'])->name('finance.reports.monthly');
        Route::get('/finance/reports/aging', [FinanceController::class, 'agingReport'])->name('finance.reports.aging');
        Route::get('/finance/pending-rf', [FinanceController::class, 'pendingRf'])->name('finance.pending-rf');
        Route::get('/finance/schedules', [FinanceController::class, 'scheduleIndex'])->name('finance.schedules');
        Route::get('/finance/schedules/{reservation}/create', [FinanceController::class, 'scheduleCreate'])->name('finance.schedule.create');
        Route::post('/finance/schedules/{reservation}', [FinanceController::class, 'scheduleStore'])->name('finance.schedule.store');
        Route::get('/finance/schedules/{reservation}', [FinanceController::class, 'scheduleShow'])->name('finance.schedule.show');
        Route::post('/finance/schedule-payment/{schedule}', [FinanceController::class, 'recordSchedulePayment'])->name('finance.schedule.record-payment');
        Route::post('/finance/reservations/{reservation}/pagibig/apply', [FinanceController::class, 'submitPagibigApplication'])->name('finance.pagibig.apply');
        Route::post('/finance/reservations/{reservation}/pagibig/loa', [FinanceController::class, 'recordLoa'])->name('finance.pagibig.loa');
        Route::post('/finance/reservations/{reservation}/pagibig/takeout', [FinanceController::class, 'recordTakeout'])->name('finance.pagibig.takeout');
        Route::post('/finance/reservations/{reservation}/pagibig/amortization', [FinanceController::class, 'startAmortization'])->name('finance.pagibig.amortization');
    });

    Route::middleware(['role:agent'])->group(function () {
        Route::get('/agent', [DashboardController::class, 'agent'])->name('agent.dashboard');
        Route::get('/agent/properties', [DashboardController::class, 'agentProperties'])->name('agent.properties');
        Route::get('/agent/reservations', [DashboardController::class, 'agentReservations'])->name('agent.reservations');
    });

    Route::middleware(['role:staff'])->group(function () {
        Route::get('/contractor', [DashboardController::class, 'contractor'])->name('contractor.dashboard');
        Route::get('/contractor/projects', [DashboardController::class, 'contractorProjects'])->name('contractor.projects');
        Route::get('/contractor/projects/{project}', [DashboardController::class, 'contractorProjectDetail'])->name('contractor.project.detail');
        Route::get('/contractor/tasks', [DashboardController::class, 'contractorTasks'])->name('contractor.tasks');
    });

    Route::middleware(['role:client'])->group(function () {
        Route::get('/client/dashboard', [DashboardController::class, 'clientDashboard'])->name('client.dashboard');
        Route::get('/client/reservations', [DashboardController::class, 'clientReservations'])->name('client.reservations');
        Route::get('/client/reservations/poll', [DashboardController::class, 'pollReservations'])->name('client.reservations.poll');
        Route::get('/client/payments', [DashboardController::class, 'clientPayments'])->name('client.payments');
        Route::get('/client/documents', [DashboardController::class, 'clientDocuments'])->name('client.documents');
        Route::get('/client/documents/poll', [DocumentController::class, 'pollChecklist'])->name('client.documents.poll');
        Route::post('/client/documents', [DocumentController::class, 'clientStore'])->name('client.documents.store');
        Route::post('/client/reservations/{reservation}/pagibig-request', [DashboardController::class, 'requestPagibig'])->name('client.pagibig.request');
        Route::get('/client/reservations/{reservation}/site-viewing', [SiteViewingController::class, 'create'])->name('site-viewing.create');
        Route::post('/client/reservations/{reservation}/site-viewing', [SiteViewingController::class, 'store'])->name('site-viewing.store');
        Route::get('/client/follow-ups', [FollowUpController::class, 'clientIndex'])->name('client.follow-ups');
        Route::get('/client/reservations/{reservation}/schedule', [FinanceController::class, 'clientSchedule'])->name('client.schedule');
        Route::get('/client/reservations/{reservation}/pagibig-schedule', [FinanceController::class, 'clientPagibigSchedule'])->name('client.pagibig-schedule');
    });
});

// Test route to verify middleware
Route::get('/test-role', function () {
    return 'Role middleware is working!';
})->middleware('role:admin');

// Legal Pages
Route::get('/terms', fn() => view('terms'))->name('terms');
Route::get('/privacy', fn() => view('privacy'))->name('privacy');

// Public Routes (no auth required)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/browse', [HomeController::class, 'browse'])->name('home.browse');
Route::get('/property/{property}', [HomeController::class, 'property'])->name('home.property');
Route::post('/inquiry', [HomeController::class, 'inquiry'])->name('home.inquiry');
Route::post('/ai-recommend', [HomeController::class, 'aiRecommend'])->name('home.ai-recommend');

// Property Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('properties', PropertyController::class);
    Route::get('/properties/search', [PropertyController::class, 'search'])->name('properties.search');
    Route::patch('/properties/{property}/toggle-featured', [PropertyController::class, 'toggleFeatured'])->name('properties.toggle-featured');
    Route::get('/properties-archived', [PropertyController::class, 'archived'])->name('properties.archived');
    Route::patch('/properties/{id}/restore', [PropertyController::class, 'restore'])->name('properties.restore');
    Route::delete('/properties/{id}/force-delete', [PropertyController::class, 'forceDelete'])->name('properties.force-delete');
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
    Route::patch('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus'])->name('reservations.update-status')->middleware('role:admin,finance,agent');
    Route::patch('/reservations/{reservation}/pagibig', [ReservationController::class, 'updatePagibig'])->name('reservations.update-pagibig');
    Route::patch('/reservations/{reservation}/mark-viewed', [ReservationController::class, 'markViewed'])->name('reservations.mark-viewed');
    Route::post('/reservations/{reservation}/upload-proof', [ReservationController::class, 'uploadProof'])->name('reservations.upload-proof');
    Route::patch('/reservations/{reservation}/set-rf-deadline', [ReservationController::class, 'setRfDeadline'])->name('reservations.set-rf-deadline');
    Route::patch('/reservations/{reservation}/verify-rf', [ReservationController::class, 'verifyRf'])->name('reservations.verify-rf');
    Route::post('/reservations/{reservation}/checklist/{index}', [ReservationController::class, 'uploadChecklistItem'])->name('client.checklist.upload')->middleware('role:client');
    Route::patch('/reservations/{reservation}/checklist/{index}/verify', [ReservationController::class, 'verifyChecklistItem'])->name('reservations.checklist.verify')->middleware('role:admin');
    Route::patch('/reservations/{reservation}/checklist/{index}/reject', [ReservationController::class, 'rejectChecklistItem'])->name('reservations.checklist.reject')->middleware('role:admin');
    Route::post('/reservations/{reservation}/checklist/{index}/not-applicable', [ReservationController::class, 'markChecklistNotApplicable'])->name('client.checklist.not-applicable')->middleware('role:client');
    Route::post('/reservations/{reservation}/client-cancel', [ReservationController::class, 'clientCancel'])->name('client.reservation.cancel')->middleware('role:client');
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
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
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
    Route::get('/documents/checker', [DocumentController::class, 'checker'])->name('documents.checker');
    Route::resource('documents', DocumentController::class);
    Route::patch('/documents/{document}/verify', [DocumentController::class, 'verify'])->name('documents.verify');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('/client/reservations/{reservation}/checklist/{key}/upload', [DocumentController::class, 'uploadChecklistItem'])->name('documents.checklist.upload');
    Route::post('/client/reservations/{reservation}/checklist/{key}/not-applicable', [DocumentController::class, 'markNotApplicable'])->name('documents.checklist.not-applicable');
    Route::patch('/client/reservations/{reservation}/checklist/{key}/verify', [DocumentController::class, 'verifyChecklistItem'])->name('documents.checklist.verify')->middleware('role:admin');
    Route::patch('/client/reservations/{reservation}/checklist/{key}/reject', [DocumentController::class, 'rejectChecklistItem'])->name('documents.checklist.reject')->middleware('role:admin');
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
    Route::get('/audit-logs/archived', [AuditLogController::class, 'archived'])->name('audit-logs.archived');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    Route::delete('/audit-logs/{auditLog}', [AuditLogController::class, 'destroy'])->name('audit-logs.destroy');
    Route::patch('/audit-logs/{auditLog}/restore', [AuditLogController::class, 'restore'])->name('audit-logs.restore');
    Route::delete('/audit-logs/{auditLog}/force-delete', [AuditLogController::class, 'forceDelete'])->name('audit-logs.force-delete');
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

// Sales Pipeline
Route::middleware(['auth', 'role:agent,admin'])->group(function () {
    Route::get('/pipeline', [SalesPipelineController::class, 'index'])->name('pipeline.index');
});

// Lead Routes
Route::middleware(['auth', 'role:agent,admin'])->group(function () {
    Route::resource('leads', LeadController::class);
    Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.status');
});

// Project Issue Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/project-issues', [ProjectIssueController::class, 'index'])->name('project-issues.index');
    Route::post('/projects/{project}/issues', [ProjectIssueController::class, 'store'])->name('project-issues.store');
    Route::patch('/project-issues/{issue}/status', [ProjectIssueController::class, 'updateStatus'])->name('project-issues.update-status');
    Route::delete('/project-issues/{issue}', [ProjectIssueController::class, 'destroy'])->name('project-issues.destroy');
});

// Follow-Up Routes (agent + admin)
Route::middleware(['auth', 'role:agent,admin'])->group(function () {
    Route::get('/follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');
    Route::get('/follow-ups/create', [FollowUpController::class, 'create'])->name('follow-ups.create');
    Route::post('/follow-ups', [FollowUpController::class, 'store'])->name('follow-ups.store');
    Route::patch('/follow-ups/{followUp}/done', [FollowUpController::class, 'markDone'])->name('follow-ups.done');
    Route::patch('/follow-ups/{followUp}/cancel', [FollowUpController::class, 'cancel'])->name('follow-ups.cancel');
    Route::delete('/follow-ups/{followUp}', [FollowUpController::class, 'destroy'])->name('follow-ups.destroy');
});

// Site Viewing Routes (agent + admin)
Route::middleware(['auth', 'role:agent,admin'])->group(function () {
    Route::get('/site-viewings', [SiteViewingController::class, 'index'])->name('site-viewing.index');
    Route::patch('/site-viewings/{siteViewing}/confirm', [SiteViewingController::class, 'confirm'])->name('site-viewing.confirm');
    Route::patch('/site-viewings/{siteViewing}/cancel', [SiteViewingController::class, 'cancel'])->name('site-viewing.cancel');
    Route::patch('/site-viewings/{siteViewing}/complete', [SiteViewingController::class, 'complete'])->name('site-viewing.complete');
});

// Agent Commission Routes
Route::middleware(['auth', 'role:admin,finance'])->group(function () {
    Route::get('/commissions', [AgentCommissionController::class, 'index'])->name('commissions.index');
    Route::post('/commissions', [AgentCommissionController::class, 'store'])->name('commissions.store');
    Route::patch('/commissions/{commission}/approve', [AgentCommissionController::class, 'approve'])->name('commissions.approve');
    Route::patch('/commissions/{commission}/paid', [AgentCommissionController::class, 'markPaid'])->name('commissions.paid');
    Route::patch('/commissions/{commission}/cancel', [AgentCommissionController::class, 'cancel'])->name('commissions.cancel');
});

// Reservation Phase 2 Routes
Route::middleware(['auth', 'role:admin,finance'])->group(function () {
    Route::patch('/reservations/{reservation}/insurance', [ReservationController::class, 'updateInsurance'])->name('reservations.update-insurance');
    Route::patch('/reservations/{reservation}/refund', [ReservationController::class, 'updateRefund'])->name('reservations.update-refund');
    Route::patch('/reservations/{reservation}/loan-reconciliation', [ReservationController::class, 'updateLoanReconciliation'])->name('reservations.update-loan-reconciliation');
    Route::patch('/reservations/{reservation}/coborrower', [ReservationController::class, 'updateCoborrower'])->name('reservations.update-coborrower');
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
