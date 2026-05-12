<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Client;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'client_id'        => 'required|exists:clients,id',
            'reservation_id'   => 'nullable|exists:reservations,id',
            'agent_id'         => 'nullable|exists:agents,id',
            'payment_type'     => 'required|string|max:100',
            'amount'           => 'required|numeric|min:0',
            'currency'         => 'required|string|max:3',
            'payment_method'   => 'required|in:cash,bank_transfer,credit_card,gcash,check,pagibig',
            'reference_number' => 'nullable|string|max:100',
            'payment_date'     => 'required|date',
            'description'      => 'nullable|string',
            'status'           => 'required|in:pending,completed,failed,cancelled',
            'proof_image'      => 'nullable|image|max:5120',
        ]);

        $data = $request->except('proof_image');
        if ($request->hasFile('proof_image')) {
            $data['proof_image'] = $request->file('proof_image')->store('payment-proofs', 'public');
        }

        Payment::create($data);

        // Check if reservation is now fully paid — notify admin
        if ($request->filled('reservation_id')) {
            $reservation = Reservation::with(['property', 'client', 'payments'])->find($request->reservation_id);
            if ($reservation) {
                $totalPaid = $reservation->payments->where('status', 'completed')->sum('amount') + $request->amount;
                $remaining = ($reservation->property->price ?? 0) - $totalPaid;
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
            }
        }

        $redirectTo = $request->filled('from_pagibig') ? route('finance.pagibig') : route('finance.payments');
        return redirect($redirectTo)->with('success', 'Payment recorded successfully.');
    }

    public function clientPayments(Request $request, Client $client)
    {
        $reservations = Reservation::with(['property', 'payments'])
            ->where('client_id', $client->id)
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
}
