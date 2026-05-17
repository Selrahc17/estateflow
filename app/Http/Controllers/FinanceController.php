<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Client;
use App\Models\Agent;
use Illuminate\Http\Request;
use App\Mail\PagibigLoaReceived;
use App\Mail\PagibigTakeoutProcessed;
use App\Mail\PagibigAmortizationStarted;
use App\Models\PagibigAmortizationSchedule;

class FinanceController extends Controller
{
    public function dashboard()
    {
        $totalPayments    = Payment::count();
        $totalCollected   = Payment::where('status', 'completed')->sum('amount');
        $pendingAmount    = Payment::where('status', 'pending')->sum('amount');
        $thisMonthTotal   = Payment::where('status', 'completed')
                            ->whereMonth('payment_date', now()->month)
                            ->whereYear('payment_date', now()->year)
                            ->sum('amount');

        $recentPayments   = Payment::with(['client', 'reservation.property'])
                            ->latest()->take(8)->get();

        $paymentsByMethod = Payment::where('status', 'completed')
                            ->selectRaw('payment_method, SUM(amount) as total')
                            ->groupBy('payment_method')
                            ->pluck('total', 'payment_method');

        return view('finance.dashboard', compact(
            'totalPayments', 'totalCollected', 'pendingAmount',
            'thisMonthTotal', 'recentPayments', 'paymentsByMethod'
        ));
    }

    public function payments(Request $request)
    {
        $query = Payment::with(['client', 'agent', 'reservation.property']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('client', fn($q2) => $q2->where('first_name', 'like', '%' . $request->search . '%')
                      ->orWhere('last_name', 'like', '%' . $request->search . '%'));
            });
        }

        if ($request->filled('status'))         $query->where('status', $request->status);
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);

        $payments       = $query->latest()->paginate(15)->withQueryString();
        $totalPayments  = Payment::count();
        $totalAmount    = Payment::where('status', 'completed')->sum('amount');
        $pendingAmount  = Payment::where('status', 'pending')->sum('amount');
        $completedCount = Payment::where('status', 'completed')->count();

        return view('finance.payments', compact(
            'payments', 'totalPayments', 'totalAmount', 'pendingAmount', 'completedCount'
        ));
    }

    public function recordPagibig(Reservation $reservation)
    {
        $reservation->load(['property', 'client', 'payments']);
        $totalPaid = $reservation->payments->where('status', 'completed')->sum('amount');
        $remaining = ($reservation->property->price ?? 0) - $totalPaid;

        return view('finance.pagibig-record', compact('reservation', 'totalPaid', 'remaining'));
    }

    public function storePagibig(Request $request, Reservation $reservation)
    {
        $request->validate([
            'check_number'   => 'nullable|string|max:100',
            'amount'         => 'required|numeric|min:1',
            'payment_date'   => 'required|date',
            'payment_type'   => 'required|in:partial,full_payment',
            'description'    => 'nullable|string|max:500',
            'proof_image'    => 'nullable|image|max:5120',
        ]);

        $reservation->load(['property', 'client', 'payments']);

        $data = [
            'client_id'        => $reservation->client_id,
            'reservation_id'   => $reservation->id,
            'payment_type'     => $request->payment_type,
            'amount'           => $request->amount,
            'currency'         => 'PHP',
            'payment_method'   => 'pagibig',
            'reference_number' => $request->check_number,
            'payment_date'     => $request->payment_date,
            'description'      => $request->description ?? 'Pag-IBIG loan payment',
            'status'           => 'completed',
        ];

        if ($request->hasFile('proof_image')) {
            $data['proof_image'] = $request->file('proof_image')->store('payment-proofs', 'public');
        }

        Payment::create($data);

        // Recalculate remaining after this payment
        $newTotalPaid = $reservation->payments->where('status', 'completed')->sum('amount') + $request->amount;
        $remaining    = ($reservation->property->price ?? 0) - $newTotalPaid;

        // Notify admin if fully paid
        if ($remaining <= 0) {
            $admins = \App\Models\User::where('role', 'admin')->where('is_active', true)->get();
            foreach ($admins as $admin) {
                \App\Models\EstateNotification::create([
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id'   => $admin->id,
                    'type'            => 'pagibig_fully_paid',
                    'title'           => 'Pag-IBIG Fully Paid — ' . ($reservation->client->full_name ?? 'Client'),
                    'message'         => ($reservation->property->title ?? 'Property') . ' is now fully paid via Pag-IBIG. Please mark the reservation as completed.',
                    'data'            => json_encode(['reservation_id' => $reservation->id]),
                ]);
            }
        }

        return redirect()->route('finance.pagibig')->with('success', 'Pag-IBIG payment recorded successfully.');
    }

    public function pagibig()
    {
        $reservations = Reservation::with(['property', 'client', 'payments'])
            ->where('pagibig_status', 'approved')
            ->where('status', 'confirmed')
            ->latest()
            ->get()
            ->map(function ($res) {
                $res->total_paid   = $res->payments->where('status', 'completed')->sum('amount');
                $res->remaining    = ($res->property->price ?? 0) - $res->total_paid;
                return $res;
            });

        return view('finance.pagibig', compact('reservations'));
    }

    public function createPayment(Request $request)
    {
        $reservations = Reservation::with(['property', 'client'])
                        ->whereIn('status', ['confirmed', 'pending'])->get();
        $clients      = Client::where('status', 'active')->get();
        $agents       = Agent::where('status', 'active')->get();
        $selectedReservation = $request->filled('reservation_id')
            ? Reservation::with(['property', 'client'])->find($request->reservation_id)
            : null;

        return view('finance.create-payment', compact(
            'reservations', 'clients', 'agents', 'selectedReservation'
        ));
    }

    public function storePayment(Request $request)
    {
        $request->validate([
            'client_id'           => 'required|exists:clients,id',
            'reservation_id'      => 'nullable|exists:reservations,id',
            'agent_id'            => 'nullable|exists:agents,id',
            'payment_schedule_id' => 'nullable|exists:payment_schedules,id',
            'payment_type'        => 'required|string|max:100',
            'amount'              => 'required|numeric|min:0',
            'payment_method'      => 'required|in:cash,bank_transfer,credit_card,check,pagibig',
            'reference_number'    => 'nullable|string|max:100',
            'receipt_number'      => 'nullable|string|max:100',
            'payment_date'        => 'required|date',
            'description'         => 'nullable|string',
            'status'              => 'nullable|in:pending,completed,failed,cancelled',
            'proof_image'         => 'nullable|image|max:5120',
        ]);

        $data = $request->except('proof_image');
        $data['currency'] = 'PHP';
        $data['status']   = $data['status'] ?? 'completed';

        if ($request->hasFile('proof_image')) {
            $data['proof_image'] = $request->file('proof_image')->store('payment-proofs', 'public');
        }

        Payment::create($data);

        // Sync payment schedule status if linked
        if ($request->filled('payment_schedule_id')) {
            $schedule = \App\Models\PaymentSchedule::find($request->payment_schedule_id);
            if ($schedule) {
                $schedule->update([
                    'amount_paid'    => (float) $schedule->amount_paid + (float) $request->amount,
                    'receipt_number' => $request->receipt_number ?? $schedule->receipt_number,
                ]);
                $schedule->syncStatus();
            }
        }

        // Notify client
        if ($request->filled('reservation_id')) {
            $reservation = Reservation::with(['property', 'client.user'])->find($request->reservation_id);
            if ($reservation?->client?->user) {
                \App\Models\EstateNotification::create([
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id'   => $reservation->client->user->id,
                    'type'            => 'payment_recorded',
                    'data'            => [
                        'title'   => 'Payment Recorded' . ($request->receipt_number ? ' — OR# ' . $request->receipt_number : ''),
                        'message' => '₱' . number_format($request->amount, 2) . ' has been recorded for ' . ($reservation->property->title ?? 'your property') . '.',
                    ],
                    'priority' => 'high',
                    'is_read'  => false,
                ]);
            }
        }

        return redirect()->route('finance.payments')->with('success', 'Payment recorded successfully.');
    }

    public function clientPayments(Request $request, Client $client)
    {
        $reservations = Reservation::with(['property', 'payments'])
            ->where('client_id', $client->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->latest()
            ->get()
            ->map(function ($res) {
                $res->total_paid    = $res->payments->where('status', 'completed')->sum('amount');
                $res->total_pending = $res->payments->where('status', 'pending')->sum('amount');
                $res->remaining     = ($res->property->price ?? 0) - $res->total_paid;
                return $res;
            });

        $totalPaid    = $reservations->sum('total_paid');
        $totalPending = $reservations->sum('total_pending');

        if ($request->filled('export')) {
            $filename = 'payments_' . str_replace(' ', '_', $client->full_name) . '_' . now()->format('Ymd') . '.csv';
            $callback = function () use ($reservations, $client, $request) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Client: ' . $client->full_name]);
                fputcsv($handle, ['Generated: ' . now()->format('M d, Y h:i A')]);
                fputcsv($handle, []);
                $filtered = $request->filled('reservation_id')
                    ? $reservations->where('id', $request->reservation_id)
                    : $reservations;
                foreach ($filtered as $res) {
                    fputcsv($handle, ['Property: ' . ($res->property->title ?? '—')]);
                    fputcsv($handle, ['#', 'Type', 'Amount', 'Method', 'Reference', 'Date', 'Status']);
                    foreach ($res->payments as $p) {
                        fputcsv($handle, [
                            $p->id,
                            ucfirst(str_replace('_', ' ', $p->payment_type)),
                            $p->amount,
                            ucfirst(str_replace('_', ' ', $p->payment_method)),
                            $p->reference_number ?? '',
                            $p->payment_date->format('Y-m-d'),
                            ucfirst($p->status),
                        ]);
                    }
                    fputcsv($handle, ['', 'Total Paid:', $res->total_paid, '', '', '', '']);
                    fputcsv($handle, []);
                }
                fclose($handle);
            };
            return response()->stream($callback, 200, [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename={$filename}",
            ]);
        }

        return view('finance.client-payments', compact('client', 'reservations', 'totalPaid', 'totalPending'));
    }

    public function reservationPayments(Request $request, Client $client, Reservation $reservation)
    {
        // Guard: reservation must belong to this client
        if ($reservation->client_id !== $client->id) {
            abort(403);
        }

        $reservation->load(['property', 'payments', 'agent']);
        $reservation->total_paid    = $reservation->payments->where('status', 'completed')->sum('amount');
        $reservation->total_pending = $reservation->payments->where('status', 'pending')->sum('amount');
        $reservation->remaining     = ($reservation->property->price ?? 0) - $reservation->total_paid;

        // CSV Export
        if ($request->filled('export') && $request->export === 'csv') {
            $filename = 'payments_' . str_replace(' ', '_', $client->full_name) . '_' . str_replace(' ', '_', $reservation->property->title ?? 'property') . '_' . now()->format('Ymd') . '.csv';
            $callback = function () use ($reservation, $client) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Client:', $client->full_name]);
                fputcsv($handle, ['Property:', $reservation->property->title ?? '—']);
                fputcsv($handle, ['Property Price:', $reservation->property->price ?? 0]);
                fputcsv($handle, ['Generated:', now()->format('M d, Y h:i A')]);
                fputcsv($handle, []);
                fputcsv($handle, ['#', 'Type', 'Amount', 'Method', 'Reference', 'Date', 'Status']);
                foreach ($reservation->payments as $p) {
                    fputcsv($handle, [
                        $p->id,
                        ucfirst(str_replace('_', ' ', $p->payment_type)),
                        $p->amount,
                        ucfirst(str_replace('_', ' ', $p->payment_method)),
                        $p->reference_number ?? '',
                        $p->payment_date->format('Y-m-d'),
                        ucfirst($p->status),
                    ]);
                }
                fputcsv($handle, []);
                fputcsv($handle, ['', 'Total Paid:', $reservation->total_paid, '', '', '', '']);
                fputcsv($handle, ['', 'Remaining:', $reservation->remaining, '', '', '', '']);
                fclose($handle);
            };
            return response()->stream($callback, 200, [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename={$filename}",
            ]);
        }

        // PDF Export
        if ($request->filled('export') && $request->export === 'pdf') {
            return view('finance.reservation-payments-pdf', compact('client', 'reservation'));
        }

        return view('finance.reservation-payments', compact('client', 'reservation'));
    }

    public function exportCsv(Request $request)
    {
        $query = Payment::with(['client', 'reservation.property']);
        if ($request->filled('status'))         $query->where('status', $request->status);
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);

        $payments = $query->latest()->get();
        $filename = 'payments_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['#', 'Client', 'Property', 'Type', 'Amount', 'Method', 'Reference', 'Date', 'Status']);
            foreach ($payments as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->client->full_name ?? '—',
                    $p->reservation->property->title ?? '—',
                    ucfirst(str_replace('_', ' ', $p->payment_type)),
                    $p->amount,
                    ucfirst(str_replace('_', ' ', $p->payment_method)),
                    $p->reference_number ?? '',
                    $p->payment_date->format('Y-m-d'),
                    ucfirst($p->status),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function exportPdf(Request $request)
    {
        $query = Payment::with(['client', 'reservation.property']);
        if ($request->filled('status'))         $query->where('status', $request->status);
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);

        $payments    = $query->latest()->get();
        $totalAmount = $payments->where('status', 'completed')->sum('amount');
        $filters     = $request->only(['search', 'status', 'payment_method']);

        return view('payments.export-pdf', compact('payments', 'totalAmount', 'filters'));
    }

    public function searchUnits(Request $request)
    {
        $q = $request->input('q', '');

        $reservations = Reservation::with([
            'property.propertyType',
            'client',
            'agent',
            'paymentSchedules',
            'payments',
        ])
        ->whereIn('status', ['reservation_paid', 'confirmed', 'completed'])
        ->where(function ($query) use ($q) {
            $query->whereHas('property', function ($pq) use ($q) {
                $pq->where('block', 'ilike', "%{$q}%")
                   ->orWhere('lot', 'ilike', "%{$q}%")
                   ->orWhere('title', 'ilike', "%{$q}%");
            })
            ->orWhereHas('client', function ($cq) use ($q) {
                $cq->where('first_name', 'ilike', "%{$q}%")
                   ->orWhere('last_name', 'ilike', "%{$q}%");
            });
        })
        ->limit(10)
        ->get();

        return response()->json($reservations->map(function ($res) {
            $schedules   = $res->paymentSchedules;
            $totalDue    = $schedules->sum('amount_due');
            $totalPaid   = $schedules->sum('amount_paid');
            $balance     = max(0, $totalDue - $totalPaid);
            $paidCount   = $schedules->where('status', 'paid')->count();
            $totalCount  = $schedules->count();

            // Next unpaid installment
            $nextInstallment = $schedules
                ->whereNotIn('status', ['paid'])
                ->sortBy('installment_number')
                ->first();

            return [
                'id'              => $res->id,
                'label'           => 'Blk ' . ($res->property->block ?? '?') . ', Lot ' . ($res->property->lot ?? '?') . ' — ' . ($res->client->full_name ?? ''),
                'block'           => $res->property->block ?? '',
                'lot'             => $res->property->lot ?? '',
                'property_title'  => $res->property->title ?? '',
                'property_type'   => $res->property->propertyType->name ?? '',
                'payment_scheme'  => $res->payment_scheme === 'pagibig' ? 'Pag-IBIG' : 'Cash / Bank Transfer',
                'client_name'     => $res->client->full_name ?? '',
                'client_phone'    => $res->client->phone ?? '',
                'agent_name'      => $res->agent->full_name ?? 'Not assigned',
                'agent_code'      => $res->agent->agent_code ?? '',
                'agent_id'        => $res->agent_id,
                'client_id'       => $res->client_id,
                'status'          => $res->status,
                'total_due'       => $totalDue,
                'total_paid'      => $totalPaid,
                'balance'         => $balance,
                'paid_count'      => $paidCount,
                'total_count'     => $totalCount,
                'next_installment' => $nextInstallment ? [
                    'id'         => $nextInstallment->id,
                    'number'     => $nextInstallment->installment_number,
                    'due_date'   => $nextInstallment->due_date->format('M d, Y'),
                    'amount_due' => $nextInstallment->amount_due,
                    'amount_paid'=> $nextInstallment->amount_paid,
                    'balance'    => max(0, (float)$nextInstallment->amount_due - (float)$nextInstallment->amount_paid),
                    'status'     => $nextInstallment->status,
                ] : null,
            ];
        }));
    }

    public function monthlyReport(Request $request)
    {
        $year  = $request->input('year', now()->year);
        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $payments = Payment::where('status', 'completed')
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', $m)
                ->get();

            $months[] = [
                'month'        => \Carbon\Carbon::create($year, $m, 1)->format('F'),
                'month_num'    => $m,
                'total'        => $payments->sum('amount'),
                'count'        => $payments->count(),
                'cash'         => $payments->where('payment_method', 'cash')->sum('amount'),
                'bank'         => $payments->where('payment_method', 'bank_transfer')->sum('amount'),
                'pagibig'      => $payments->where('payment_method', 'pagibig')->sum('amount'),
            ];
        }

        $yearTotal = collect($months)->sum('total');
        $years     = range(now()->year, now()->year - 4);

        return view('finance.reports.monthly', compact('months', 'year', 'yearTotal', 'years'));
    }

    public function agingReport()
    {
        $overdue = \App\Models\PaymentSchedule::with(['reservation.client', 'reservation.property'])
            ->where('status', 'overdue')
            ->orderBy('due_date')
            ->get()
            ->map(function ($s) {
                $s->days_overdue = $s->due_date->diffInDays(now());
                $s->balance      = max(0, (float)$s->amount_due - (float)$s->amount_paid);
                return $s;
            });

        $pagibigOverdue = \App\Models\PagibigAmortizationSchedule::with(['reservation.client', 'reservation.property'])
            ->where('status', 'overdue')
            ->orderBy('due_date')
            ->get()
            ->map(function ($s) {
                $s->days_overdue = $s->due_date->diffInDays(now());
                $s->balance      = max(0, (float)$s->amount_due - (float)$s->amount_paid);
                return $s;
            });

        $totalOverdueEquity  = $overdue->sum('balance');
        $totalOverduePagibig = $pagibigOverdue->sum('balance');

        return view('finance.reports.aging', compact(
            'overdue', 'pagibigOverdue', 'totalOverdueEquity', 'totalOverduePagibig'
        ));
    }

    public function pendingRf()
    {
        $reservations = Reservation::with(['property', 'client'])
            ->where('status', 'confirmed')
            ->where('viewing_status', 'payment_uploaded')
            ->latest()
            ->get();

        return view('finance.pending-rf', compact('reservations'));
    }

    // ── Payment Schedule ──

    public function scheduleIndex()
    {
        $reservations = Reservation::with(['property', 'client', 'paymentSchedules'])
            ->whereIn('status', ['reservation_paid', 'completed'])
            ->latest()
            ->paginate(15);

        return view('finance.schedules', compact('reservations'));
    }

    public function scheduleCreate(Reservation $reservation)
    {
        $reservation->load(['property', 'client', 'paymentSchedules']);
        return view('finance.schedule-create', compact('reservation'));
    }

    public function scheduleStore(Request $request, Reservation $reservation)
    {
        $request->validate([
            'installments'              => 'required|array|min:1',
            'installments.*.due_date'   => 'required|date',
            'installments.*.amount_due' => 'required|numeric|min:1',
        ]);

        $reservation->paymentSchedules()->delete();

        foreach ($request->installments as $i => $row) {
            \App\Models\PaymentSchedule::create([
                'reservation_id'     => $reservation->id,
                'installment_number' => $i + 1,
                'due_date'           => $row['due_date'],
                'amount_due'         => $row['amount_due'],
                'amount_paid'        => 0,
                'status'             => 'upcoming',
                'notes'              => $row['notes'] ?? null,
            ]);
        }

        $reservation->paymentSchedules()->get()->each->syncStatus();

        $clientUser = $reservation->client?->user;
        if ($clientUser) {
            \App\Models\EstateNotification::create([
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'payment_schedule_issued',
                'data'            => [
                    'title'   => 'Payment Schedule Issued',
                    'message' => 'Your equity payment schedule for ' . $reservation->property->title . ' has been issued.',
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        return redirect()->route('finance.schedule.show', $reservation)
            ->with('success', 'Payment schedule created successfully.');
    }

    public function scheduleShow(Reservation $reservation)
    {
        $reservation->load(['property', 'client', 'paymentSchedules.payments']);
        $reservation->paymentSchedules->each->syncStatus();
        $reservation->refresh()->load('paymentSchedules.payments');

        $totalDue  = $reservation->paymentSchedules->sum('amount_due');
        $totalPaid = $reservation->paymentSchedules->sum('amount_paid');
        $remaining = $totalDue - $totalPaid;

        return view('finance.schedule-show', compact('reservation', 'totalDue', 'totalPaid', 'remaining'));
    }

    public function recordSchedulePayment(Request $request, \App\Models\PaymentSchedule $schedule)
    {
        $request->validate([
            'amount'           => 'required|numeric|min:1',
            'payment_method'   => 'required|in:cash,bank_transfer,check,pagibig',
            'payment_date'     => 'required|date',
            'receipt_number'   => 'required|string|max:100',
            'reference_number' => 'nullable|string|max:100',
            'proof_image'      => 'nullable|image|max:5120',
        ]);

        $schedule->load('reservation.client');

        $data = [
            'reservation_id'      => $schedule->reservation_id,
            'payment_schedule_id' => $schedule->id,
            'client_id'           => $schedule->reservation->client_id,
            'payment_type'        => 'equity_installment',
            'amount'              => $request->amount,
            'currency'            => 'PHP',
            'payment_method'      => $request->payment_method,
            'reference_number'    => $request->reference_number,
            'receipt_number'      => $request->receipt_number,
            'payment_date'        => $request->payment_date,
            'description'         => 'Installment #' . $schedule->installment_number,
            'status'              => 'completed',
        ];

        if ($request->hasFile('proof_image')) {
            $data['proof_image'] = $request->file('proof_image')->store('payment-proofs', 'public');
        }

        \App\Models\Payment::create($data);

        $newPaid = (float) $schedule->amount_paid + (float) $request->amount;
        $schedule->update([
            'amount_paid'    => $newPaid,
            'receipt_number' => $request->receipt_number,
        ]);
        $schedule->syncStatus();

        $clientUser = $schedule->reservation->client?->user;
        if ($clientUser) {
            \App\Models\EstateNotification::create([
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'installment_recorded',
                'data'            => [
                    'title'   => 'Payment Recorded — OR# ' . $request->receipt_number,
                    'message' => '₱' . number_format($request->amount, 2) . ' for Installment #' . $schedule->installment_number . ' recorded. OR# ' . $request->receipt_number,
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', 'Payment recorded. OR# ' . $request->receipt_number);
    }

    // ── Pag-IBIG Loan Tracking ──

    public function submitPagibigApplication(Reservation $reservation)
    {
        if ($reservation->payment_scheme !== 'pagibig') {
            return back()->with('error', 'This reservation is not under Pag-IBIG scheme.');
        }
        if ($reservation->status !== 'reservation_paid') {
            return back()->with('error', 'Reservation must be in RF Paid status before submitting a Pag-IBIG application.');
        }
        if (!$reservation->isEquityFullyPaid()) {
            return back()->with('error', 'All equity installments must be fully paid before submitting a Pag-IBIG application.');
        }
        if ($reservation->pagibig_loan_status) {
            return back()->with('error', 'Pag-IBIG application has already been submitted.');
        }

        $reservation->update([
            'pagibig_loan_status' => 'applied',
            'pagibig_applied_at'  => now(),
            'status'              => 'pagibig_applied',
        ]);

        $clientUser = $reservation->client?->user;
        if ($clientUser) {
            \App\Models\EstateNotification::create([
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'pagibig_application_submitted',
                'data'            => [
                    'title'   => 'Pag-IBIG Application Submitted',
                    'message' => 'Your Pag-IBIG loan application for ' . $reservation->property->title . ' has been submitted to HDMF.',
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', 'Pag-IBIG application submitted to HDMF.');
    }

    public function recordLoa(Request $request, Reservation $reservation)
    {
        if ($reservation->pagibig_loan_status !== 'applied') {
            return back()->with('error', 'Application must be submitted before recording LOA.');
        }

        $request->validate([
            'pagibig_loa_number' => 'required|string|max:100',
        ]);

        $reservation->update([
            'pagibig_loan_status' => 'approved',
            'pagibig_approved_at' => now(),
            'pagibig_loa_number'  => $request->pagibig_loa_number,
            'status'              => 'pagibig_approved',
        ]);

        $clientUser = $reservation->client?->user;
        if ($clientUser) {
            \App\Models\EstateNotification::create([
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'pagibig_loa_received',
                'data'            => [
                    'title'   => 'Letter of Approval Received',
                    'message' => 'Your Pag-IBIG loan for ' . $reservation->property->title . ' has been approved. LOA#: ' . $request->pagibig_loa_number,
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
            if ($clientUser->email) {
                try { Mail::to($clientUser->email)->send(new PagibigLoaReceived($reservation->fresh(['client', 'property']))); } catch (\Exception $e) {}
            }
        }

        return back()->with('success', 'LOA recorded. LOA# ' . $request->pagibig_loa_number);
    }

    public function recordTakeout(Request $request, Reservation $reservation)
    {
        if ($reservation->pagibig_loan_status !== 'approved') {
            return back()->with('error', 'LOA must be received before recording takeout.');
        }

        $request->validate([
            'pagibig_takeout_amount' => 'required|numeric|min:1',
            'pagibig_takeout_at'     => 'required|date',
        ]);

        $reservation->update([
            'pagibig_loan_status'    => 'takeout',
            'pagibig_takeout_at'     => $request->pagibig_takeout_at,
            'pagibig_takeout_amount' => $request->pagibig_takeout_amount,
            'status'                 => 'pagibig_takeout',
        ]);

        $clientUser = $reservation->client?->user;
        if ($clientUser) {
            \App\Models\EstateNotification::create([
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'pagibig_takeout_processed',
                'data'            => [
                    'title'   => 'Pag-IBIG Takeout Processed',
                    'message' => 'Loan proceeds of ₱' . number_format($request->pagibig_takeout_amount, 2) . ' for ' . $reservation->property->title . ' have been released.',
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
            if ($clientUser->email) {
                try { Mail::to($clientUser->email)->send(new PagibigTakeoutProcessed($reservation->fresh(['client', 'property']))); } catch (\Exception $e) {}
            }
        }

        return back()->with('success', 'Takeout recorded. ₱' . number_format($request->pagibig_takeout_amount, 2) . ' released.');
    }

    public function startAmortization(Request $request, Reservation $reservation)
    {
        if ($reservation->pagibig_loan_status !== 'takeout') {
            return back()->with('error', 'Takeout must be processed before starting amortization.');
        }

        $request->validate([
            'pagibig_amortization_start'   => 'required|date',
            'pagibig_monthly_amortization' => 'required|numeric|min:1',
            'loan_term_years'              => 'required|integer|in:20,25,30',
        ]);

        $totalMonths = (int) $request->loan_term_years * 12;

        $reservation->update([
            'pagibig_loan_status'          => 'amortization',
            'pagibig_amortization_start'   => $request->pagibig_amortization_start,
            'pagibig_monthly_amortization' => $request->pagibig_monthly_amortization,
            'pagibig_loan_term_years'      => $request->loan_term_years,
            'status'                       => 'pagibig_amortization',
        ]);

        $reservation->pagibigAmortizationSchedules()->delete();
        $startDate = \Carbon\Carbon::parse($request->pagibig_amortization_start);
        for ($i = 0; $i < $totalMonths; $i++) {
            PagibigAmortizationSchedule::create([
                'reservation_id' => $reservation->id,
                'month_number'   => $i + 1,
                'due_date'       => $startDate->copy()->addMonths($i)->toDateString(),
                'amount_due'     => $request->pagibig_monthly_amortization,
                'amount_paid'    => 0,
                'status'         => 'upcoming',
            ]);
        }

        $clientUser = $reservation->client?->user;
        if ($clientUser) {
            \App\Models\EstateNotification::create([
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'pagibig_amortization_started',
                'data'            => [
                    'title'   => 'Monthly Amortization Active',
                    'message' => 'Your monthly amortization of ₱' . number_format($request->pagibig_monthly_amortization, 2) . ' for ' . $reservation->property->title . ' starts on ' . $startDate->format('M d, Y') . '. Pay directly to Pag-IBIG.',
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
            if ($clientUser->email) {
                try { Mail::to($clientUser->email)->send(new PagibigAmortizationStarted($reservation->fresh(['client', 'property']))); } catch (\Exception $e) {}
            }
        }

        return back()->with('success', 'Amortization started. Monthly amount: ₱' . number_format($request->pagibig_monthly_amortization, 2));
    }

    public function clientSchedule(Reservation $reservation)
    {
        $clientRecord = \App\Models\Client::where('user_id', auth()->id())->first();
        if (!$clientRecord || $reservation->client_id !== $clientRecord->id) {
            abort(403);
        }

        $reservation->load(['property', 'paymentSchedules.payments']);
        $reservation->paymentSchedules->each->syncStatus();
        $reservation->refresh()->load('paymentSchedules.payments');

        $totalDue  = $reservation->paymentSchedules->sum('amount_due');
        $totalPaid = $reservation->paymentSchedules->sum('amount_paid');
        $remaining = $totalDue - $totalPaid;

        return view('client.schedule', compact('reservation', 'totalDue', 'totalPaid', 'remaining'));
    }

    public function clientPagibigSchedule(Reservation $reservation)
    {
        $clientRecord = \App\Models\Client::where('user_id', auth()->id())->first();
        if (!$clientRecord || $reservation->client_id !== $clientRecord->id) {
            abort(403);
        }

        $reservation->load(['property', 'pagibigAmortizationSchedules']);
        $schedules = $reservation->pagibigAmortizationSchedules()->orderBy('month_number')->get();
        $schedules->each->syncStatus();
        $schedules = $reservation->pagibigAmortizationSchedules()->orderBy('month_number')->get();

        $totalDue  = $schedules->sum('amount_due');
        $totalPaid = $schedules->sum('amount_paid');

        return view('client.pagibig-schedule', compact('reservation', 'schedules', 'totalDue', 'totalPaid'));
    }
}
