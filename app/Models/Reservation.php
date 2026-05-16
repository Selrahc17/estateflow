<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'property_id', 'client_id', 'agent_id', 'reservation_date',
        'expiry_date', 'reservation_fee', 'rf_deadline', 'rf_paid_at', 'rf_or_number',
        'status', 'notes',
        'payment_scheme', 'employment_type',
        'coborrower_name', 'coborrower_relationship', 'coborrower_contact',
        'document_checklist',
        'pagibig_status', 'pagibig_reference',
        'pagibig_loan_status', 'pagibig_applied_at', 'pagibig_approved_at',
        'pagibig_loa_number', 'pagibig_takeout_at', 'pagibig_takeout_amount',
        'pagibig_amortization_start', 'pagibig_monthly_amortization',
        'cancelled_at', 'cancellation_reason', 'cancellation_type', 'data_wiped_at',
        'viewing_status', 'proof_of_payment', 'viewed_at', 'payment_uploaded_at',
    ];

    public const PAGIBIG_LOAN_STATUSES = [
        'applied'      => 'Application Submitted',
        'approved'     => 'Letter of Approval Received',
        'takeout'      => 'Takeout Processed',
        'amortization' => 'Monthly Amortization Active',
    ];

    // All valid reservation statuses in order of flow
    public const STATUSES = [
        'pending'              => 'Pending',
        'confirmed'            => 'Confirmed',
        'reservation_paid'     => 'Reservation Paid',
        'pagibig_applied'      => 'Pag-IBIG Applied',
        'pagibig_approved'     => 'Pag-IBIG Approved',
        'pagibig_takeout'      => 'Pag-IBIG Takeout',
        'pagibig_amortization' => 'Pag-IBIG Amortization',
        'completed'            => 'Completed',
        'cancelled'            => 'Cancelled',
        'expired'              => 'Expired',
    ];

    public const PAYMENT_SCHEMES = [
        'cash_bank' => 'Cash / Bank Transfer',
        'pagibig'   => 'Pag-IBIG',
    ];

    public const EMPLOYMENT_TYPES = [
        'locally_employed'            => 'Locally Employed',
        'locally_employed_coborrower' => 'Locally Employed with Co-Borrower',
        'self_employed'               => 'Self Employed',
        'ofw'                         => 'OFW',
        'ofw_coborrower'              => 'OFW with Co-Borrower',
    ];

    public const COBORROWER_TYPES = [
        'locally_employed_coborrower',
        'ofw_coborrower',
    ];

    public const VIEWING_STATUSES = [
        'pending'          => 'Pending Viewing',
        'viewed'           => 'Viewed',
        'payment_uploaded' => 'Payment Uploaded',
        'verified'         => 'Payment Verified',
    ];

    public const PAGIBIG_STATUSES = [
        'not_applied' => 'Not Applied',
        'applied'     => 'Applied',
        'verified'    => 'Verified',
        'approved'    => 'Approved',
        'released'    => 'Released',
    ];

    protected $casts = [
        'reservation_date'           => 'date',
        'expiry_date'                => 'date',
        'rf_deadline'                => 'date',
        'rf_paid_at'                 => 'datetime',
        'reservation_fee'            => 'decimal:2',
        'cancelled_at'               => 'datetime',
        'data_wiped_at'              => 'datetime',
        'viewed_at'                  => 'datetime',
        'payment_uploaded_at'        => 'datetime',
        'document_checklist'         => 'array',
        'pagibig_applied_at'         => 'datetime',
        'pagibig_approved_at'        => 'datetime',
        'pagibig_takeout_at'         => 'datetime',
        'pagibig_takeout_amount'     => 'decimal:2',
        'pagibig_amortization_start' => 'date',
        'pagibig_monthly_amortization' => 'decimal:2',
    ];

    public function isEquityFullyPaid(): bool
    {
        $schedules = $this->paymentSchedules;
        return $schedules->count() > 0 && $schedules->every(fn($s) => $s->status === 'paid');
    }

    public function isRfOverdue(): bool
    {
        return $this->rf_deadline
            && !$this->rf_paid_at
            && $this->rf_deadline->isPast()
            && $this->status === 'confirmed';
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function siteViewingSchedules()
    {
        return $this->hasMany(SiteViewingSchedule::class);
    }

    // cancelled_at, cancellation_reason, cancellation_type are set explicitly in controller/command

    public function isPastGracePeriod(): bool
    {
        if (!$this->cancelled_at) return false;
        return $this->cancelled_at->addDays(config('retention.grace_period_days', 7))->isPast();
    }

    public function gracePeriodEndsAt(): ?\Carbon\Carbon
    {
        if (!$this->cancelled_at) return null;
        return $this->cancelled_at->addDays(config('retention.grace_period_days', 7));
    }
}
