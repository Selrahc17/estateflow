<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class RetentionController extends Controller
{
    public function index()
    {
        $graceDays = config('retention.grace_period_days', 7);
        $cutoff    = now()->subDays($graceDays);

        // Cancelled, not yet wiped, still within grace period
        $gracePeriodReservations = Reservation::with(['client', 'property'])
            ->where('status', 'cancelled')
            ->whereNotNull('cancelled_at')
            ->where('cancelled_at', '>', $cutoff)
            ->whereNull('data_wiped_at')
            ->latest('cancelled_at')
            ->get();

        // Cancelled, not yet wiped, grace period passed (overdue)
        $overdueReservations = Reservation::with(['client', 'property'])
            ->where('status', 'cancelled')
            ->whereNotNull('cancelled_at')
            ->where('cancelled_at', '<=', $cutoff)
            ->whereNull('data_wiped_at')
            ->latest('cancelled_at')
            ->get();

        // Already wiped
        $wipedReservations = Reservation::with(['property'])
            ->whereNotNull('data_wiped_at')
            ->latest('data_wiped_at')
            ->paginate(10);

        $pendingWipe  = $gracePeriodReservations->count();
        $overdueWipe  = $overdueReservations->count();
        $wipedCount   = Reservation::whereNotNull('data_wiped_at')->count();

        return view('admin.retention', compact(
            'gracePeriodReservations', 'overdueReservations',
            'wipedReservations', 'pendingWipe', 'overdueWipe', 'wipedCount'
        ));
    }

    public function run()
    {
        Artisan::call('retention:wipe');
        $output = Artisan::output();

        return redirect()->route('retention.index')
            ->with('success', 'Data wipe completed. ' . trim($output));
    }
}
