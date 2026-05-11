<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Report — EstateFlow</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1f2937; padding: 32px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 2px solid #4f46e5; padding-bottom: 16px; }
        .logo { font-size: 20px; font-weight: bold; color: #4f46e5; }
        .logo span { font-size: 11px; font-weight: normal; color: #6b7280; display: block; margin-top: 2px; }
        .meta { text-align: right; color: #6b7280; font-size: 11px; }
        .filters { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 14px; margin-bottom: 20px; font-size: 11px; color: #6b7280; }
        .filters strong { color: #374151; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #4f46e5; color: white; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: 600; }
        .badge-completed { background: #d1fae5; color: #065f46; }
        .badge-pending   { background: #fef3c7; color: #92400e; }
        .badge-failed    { background: #fee2e2; color: #991b1b; }
        .badge-cancelled { background: #f3f4f6; color: #4b5563; }
        .summary { border-top: 2px solid #e5e7eb; padding-top: 14px; display: flex; justify-content: flex-end; gap: 40px; }
        .summary-item { text-align: right; }
        .summary-item .label { font-size: 11px; color: #6b7280; }
        .summary-item .value { font-size: 16px; font-weight: bold; color: #4f46e5; }
        .footer { margin-top: 32px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 12px; }
        @media print {
            body { padding: 16px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="logo">
        EstateFlow
        <span>Payment Report</span>
    </div>
    <div class="meta">
        Generated: {{ now()->format('F j, Y g:i A') }}<br>
        Total Records: {{ $payments->count() }}
        @if(!empty(array_filter($filters)))
            <br>Filtered Report
        @endif
    </div>
</div>

@if(!empty(array_filter($filters)))
<div class="filters">
    <strong>Filters applied:</strong>
    @if(!empty($filters['search']))   Search: <strong>{{ $filters['search'] }}</strong> &nbsp; @endif
    @if(!empty($filters['status']))   Status: <strong>{{ ucfirst($filters['status']) }}</strong> &nbsp; @endif
    @if(!empty($filters['payment_method'])) Method: <strong>{{ ucfirst(str_replace('_', ' ', $filters['payment_method'])) }}</strong> @endif
</div>
@endif

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Client</th>
            <th>Property</th>
            <th>Type</th>
            <th>Method</th>
            <th>Reference</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($payments as $i => $p)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $p->client->full_name ?? '—' }}</td>
            <td>{{ $p->reservation->property->title ?? '—' }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $p->payment_type)) }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}</td>
            <td>{{ $p->reference_number ?? '—' }}</td>
            <td>{{ $p->payment_date->format('M d, Y') }}</td>
            <td><strong>₱{{ number_format($p->amount, 2) }}</strong></td>
            <td><span class="badge badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center;padding:20px;color:#9ca3af;">No payments found.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="summary">
    <div class="summary-item">
        <div class="label">Total Collected (Completed)</div>
        <div class="value">₱{{ number_format($totalAmount, 2) }}</div>
    </div>
</div>

<div class="footer">
    EstateFlow — Confidential Payment Report &nbsp;|&nbsp; {{ now()->format('Y') }}
</div>

<div class="no-print" style="margin-top:24px;text-align:center;">
    <button onclick="window.print()"
        style="background:#4f46e5;color:white;border:none;padding:10px 28px;border-radius:8px;font-size:13px;cursor:pointer;margin-right:8px;">
        🖨 Print / Save as PDF
    </button>
    <button onclick="window.close()"
        style="background:#f3f4f6;color:#374151;border:none;padding:10px 20px;border-radius:8px;font-size:13px;cursor:pointer;">
        Close
    </button>
</div>

<script>
    // Auto-open print dialog after a short delay
    setTimeout(() => window.print(), 500);
</script>
</body>
</html>
