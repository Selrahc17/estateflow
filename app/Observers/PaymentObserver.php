<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Payment;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        AuditLog::log(
            'payment_created',
            $payment,
            "Payment of ₱" . number_format($payment->amount, 2) . " recorded for client ID {$payment->client_id}.",
            [],
            $payment->only(['client_id', 'reservation_id', 'amount', 'payment_method', 'status'])
        );
    }

    public function updated(Payment $payment): void
    {
        $dirty = $payment->getDirty();
        if (empty($dirty)) return;

        $old = array_intersect_key($payment->getOriginal(), $dirty);

        $description = isset($dirty['status'])
            ? "Payment #{$payment->id} status changed from \"{$old['status']}\" to \"{$dirty['status']}\"."
            : "Payment #{$payment->id} of ₱" . number_format($payment->amount, 2) . " was updated.";

        AuditLog::log('payment_updated', $payment, $description, $old, $dirty);
    }

    public function deleted(Payment $payment): void
    {
        AuditLog::log(
            'payment_deleted',
            $payment,
            "Payment #{$payment->id} of ₱" . number_format($payment->amount, 2) . " was deleted.",
            $payment->only(['client_id', 'reservation_id', 'amount', 'status']),
            []
        );
    }
}
