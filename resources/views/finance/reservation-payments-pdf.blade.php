<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Report — {{ $reservation->property->title ?? 'Property' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1f2937; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 2px solid #4f46e5; padding-bottom: 16px; }
        .header h1 { font-size: 20px; color: #4f46e5; font-weight: bold; }
        .header p { font-size: 11px; color: #6b7280; margin-top: 2px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
        .info-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; }
        .info-box label { font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px; }
        .info-box p { font-size: 13px; font-weight: bold; color: #1f2937; }
        .summary { display: flex; gap: 16px; margin-bottom: 20px; }
        .summary-item { flex: 1; background: #f9fafb; border-radius: 6px; padding: 12px; text-align: center; border: 1px solid #e5e7eb; }
        .summary-item .label { font-size: 10px; color: #6b7280; margin-bottom: 4px; }
        .summary-item .value { font-size: 15px; font-weight: bold; }
        .green { color: #16a34a; }
        .red { color: #dc2626; }
        .yellow { color: #d97706; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead { background: #4f46e5; }
        thead th { color: white; text-align: left; padding: 8px 10px; font-size: 11px; font-weight: 600; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: 600; }
        .badge-green { background: #dcfce7; color: #16a34a; }
        .badge-yellow { background: #fef9c3; color: #d97706; }
        .badge-red { background: #fee2e2; color: #dc2626; }
        .badge-gray { background: #f3f4f6; color: #6b7280; }
        .totals { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; text-align: right; }
        .totals p { margin-bottom: 4px; font-size: 12px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div>
            <h1>EstateFlow</h1>
            <p>Villa Rosalina Realty & Devt. Corp.</p>
        </div>
        <div style="text-align:right">
            <p style="font-size:14px;font-weight:bold;color:#1f2937">Payment Report</p>
            <p>Generated: {{ now()->format('M d, Y h:i A') }}</p>
        </div>
    </div>

    {{-- Info --}}
    <div class="info-grid">
        <div class="info-box">
            <label>Client</label>
            <p>{{ $client->full_name }}</p>
            <p style="font-size:11px;font-weight:normal;color:#6b7280;margin-top:2px">{{ $client->phone ?? '' }} {{ $client->email ?? '' }}</p>
        </div>
        <div class="info-box">
            <label>Property</label>
            <p>{{ $reservation->property->title ?? '—' }}</p>
            <p style="font-size:11px;font-weight:normal;color:#6b7280;margin-top:2px">{{ $reservation->property->location ?? '' }}</p>
        </div>
        <div class="info-box">
            <label>Reservation Date</label>
            <p>{{ $reservation->reservation_date->format('M d, Y') }}</p>
        </div>
        <div class="info-box">
            <label>Reservation Status</label>
            <p>{{ ucfirst($reservation->status) }}</p>
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary">
        <div class="summary-item">
            <div class="label">Property Price</div>
            <div class="value">₱{{ number_format($reservation->property->price ?? 0, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Paid</div>
            <div class="value green">₱{{ number_format($reservation->total_paid, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Pending</div>
            <div class="value yellow">₱{{ number_format($reservation->total_pending, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Remaining Balance</div>
            <div class="value {{ $reservation->remaining <= 0 ? 'green' : 'red' }}">
                {{ $reservation->remaining <= 0 ? 'Fully Paid' : '₱' . number_format($reservation->remaining, 2) }}
            </div>
        </div>
    </div>

    {{-- Payments Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Reference</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservation->payments as $i => $payment)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</td>
                <td><strong>₱{{ number_format($payment->amount, 2) }}</strong></td>
                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                <td>{{ $payment->reference_number ?? '—' }}</td>
                <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                <td>
                    <span class="badge
                        {{ $payment->status === 'completed' ? 'badge-green'  : '' }}
                        {{ $payment->status === 'pending'   ? 'badge-yellow' : '' }}
                        {{ $payment->status === 'failed'    ? 'badge-red'    : '' }}
                        {{ $payment->status === 'cancelled' ? 'badge-gray'   : '' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:#9ca3af;padding:20px">No payments recorded.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <p>Total Paid: <strong class="green">₱{{ number_format($reservation->total_paid, 2) }}</strong></p>
        @if($reservation->total_pending > 0)
        <p>Pending: <strong class="yellow">₱{{ number_format($reservation->total_pending, 2) }}</strong></p>
        @endif
        <p>Remaining Balance:
            <strong class="{{ $reservation->remaining <= 0 ? 'green' : 'red' }}">
                {{ $reservation->remaining <= 0 ? 'Fully Paid ✓' : '₱' . number_format($reservation->remaining, 2) }}
            </strong>
        </p>
    </div>

    <div class="footer">
        <p>EstateFlow — Villa Rosalina Realty & Devt. Corp. | This is a system-generated document.</p>
    </div>

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
