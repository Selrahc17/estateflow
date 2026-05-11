<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Reservation;

class ReservationObserver
{
    public function created(Reservation $reservation): void
    {
        AuditLog::log(
            'reservation_created',
            $reservation,
            "Reservation #{$reservation->id} created for property ID {$reservation->property_id}.",
            [],
            $reservation->only(['property_id', 'client_id', 'agent_id', 'status', 'reservation_fee'])
        );
    }

    public function updated(Reservation $reservation): void
    {
        $dirty = $reservation->getDirty();
        if (empty($dirty)) return;

        $old = array_intersect_key($reservation->getOriginal(), $dirty);

        // Highlight status changes in the description
        $description = isset($dirty['status'])
            ? "Reservation #{$reservation->id} status changed from \"{$old['status']}\" to \"{$dirty['status']}\"."
            : "Reservation #{$reservation->id} was updated.";

        AuditLog::log('reservation_updated', $reservation, $description, $old, $dirty);
    }

    public function deleted(Reservation $reservation): void
    {
        AuditLog::log(
            'reservation_deleted',
            $reservation,
            "Reservation #{$reservation->id} for property ID {$reservation->property_id} was deleted.",
            $reservation->only(['property_id', 'client_id', 'status']),
            []
        );
    }
}
