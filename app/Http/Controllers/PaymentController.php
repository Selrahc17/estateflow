<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Client;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['client', 'agent', 'reservation.property']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('client', function ($q2) use ($request) {
                      $q2->where('first_name', 'like', '%' . $request->search . '%')
                         ->orWhere('last_name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments        = $query->latest()->paginate(15)->withQueryString();
        $totalPayments   = Payment::count();
        $totalAmount     = Payment::where('status', 'completed')->sum('amount');
        $pendingAmount   = Payment::where('status', 'pending')->sum('amount');
        $completedCount  = Payment::where('status', 'completed')->count();

        return view('payments.index', compact(
            'payments', 'totalPayments', 'totalAmount', 'pendingAmount', 'completedCount'
        ));
    }

    public function create(Request $request)
    {
        $reservations = Reservation::with(['property', 'client'])->whereIn('status', ['confirmed', 'pending'])->get();
        $clients      = Client::where('status', 'active')->get();
        $agents       = Agent::where('status', 'active')->get();
        $selectedReservation = $request->filled('reservation_id')
            ? Reservation::with(['property', 'client'])->find($request->reservation_id)
            : null;

        return view('payments.create', compact('reservations', 'clients', 'agents', 'selectedReservation'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'reservation_id'   => 'nullable|exists:reservations,id',
            'agent_id'         => 'nullable|exists:agents,id',
            'payment_type'     => 'required|string|max:100',
            'amount'           => 'required|numeric|min:0',
            'currency'         => 'required|string|max:3',
            'payment_method'   => 'required|in:cash,bank_transfer,credit_card,check,pagibig',
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

        $redirectTo = $request->filled('reservation_id')
            ? route('reservations.show', $request->reservation_id)
            : route('payments.index');

        return redirect($redirectTo)->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['client', 'agent', 'reservation.property']);
        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $reservations = Reservation::with(['property', 'client'])->get();
        $clients      = Client::where('status', 'active')->get();
        $agents       = Agent::where('status', 'active')->get();
        return view('payments.edit', compact('payment', 'reservations', 'clients', 'agents'));
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'reservation_id'   => 'nullable|exists:reservations,id',
            'agent_id'         => 'nullable|exists:agents,id',
            'payment_type'     => 'required|string|max:100',
            'amount'           => 'required|numeric|min:0',
            'currency'         => 'required|string|max:3',
            'payment_method'   => 'required|in:cash,bank_transfer,credit_card,check,pagibig',
            'reference_number' => 'nullable|string|max:100',
            'payment_date'     => 'required|date',
            'description'      => 'nullable|string',
            'status'           => 'required|in:pending,completed,failed,cancelled',
            'proof_image'      => 'nullable|image|max:5120',
        ]);

        $data = $request->except('proof_image');
        if ($request->hasFile('proof_image')) {
            if ($payment->proof_image) {
                Storage::disk('public')->delete($payment->proof_image);
            }
            $data['proof_image'] = $request->file('proof_image')->store('payment-proofs', 'public');
        }

        $payment->update($data);

        return redirect()->route('payments.index')->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return back()->with('success', 'Payment deleted successfully.');
    }

    public function exportCsv(Request $request)
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

        $payments = $query->latest()->get();

        $filename = 'payments_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=".$filename,
        ];

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

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
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

        $payments    = $query->latest()->get();
        $totalAmount = $payments->where('status', 'completed')->sum('amount');
        $filters     = $request->only(['search', 'status', 'payment_method']);

        return view('payments.export-pdf', compact('payments', 'totalAmount', 'filters'));
    }
}
