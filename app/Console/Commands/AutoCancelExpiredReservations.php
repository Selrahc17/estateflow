<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\EstateNotification;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Console\Command;

class AutoCancelExpiredReservations extends Command
{
    protected $signature   = 'reservations:auto-cancel';
    protected $description = 'Auto-cancel reservations that have expired due to no action or RF deadline passed.';

    public function handle(): void
    {
        $this->cancelNoActionReservations();
        $this->cancelRfExpiredReservations();
    }

    // Trigger 1: pending reservations with no action for 7 days
    private function cancelNoActionReservations(): void
    {
        $cutoff = now()->subDays(7);

        $reservations = Reservation::with(['property', 'client.user'])
            ->where('status', 'pending')
            ->where('created_at', '<=', $cutoff)
            ->get();

        foreach ($reservations as $reservation) {
            $this->cancelReservation(
                $reservation,
                'auto_no_action',
                'Automatically cancelled — no action taken within 7 days of submission.'
            );
        }

        $this->info("Trigger 1: cancelled {$reservations->count()} no-action reservation(s).");
    }

    // Trigger 2: confirmed reservations where RF deadline has passed with no payment uploaded
    private function cancelRfExpiredReservations(): void
    {
        $reservations = Reservation::with(['property', 'client.user'])
            ->where('status', 'confirmed')
            ->whereNotNull('rf_deadline')
            ->where('rf_deadline', '<', now()->toDateString())
            ->whereNotIn('viewing_status', ['payment_uploaded', 'verified'])
            ->get();

        foreach ($reservations as $reservation) {
            $this->cancelReservation(
                $reservation,
                'auto_rf_expired',
                'Automatically cancelled — RF payment deadline passed with no proof of payment uploaded.'
            );
        }

        $this->info("Trigger 2: cancelled {$reservations->count()} RF-expired reservation(s).");
    }

    private function cancelReservation(Reservation $reservation, string $type, string $reason): void
    {
        // Release property back to available
        if ($reservation->property) {
            $reservation->property->update(['status' => 'available']);
        }

        $reservation->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_type'   => $type,
            'cancellation_reason' => $reason,
        ]);

        // Notify client
        $clientUser = $reservation->client?->user;
        if ($clientUser) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'reservation_auto_cancelled',
                'data'            => [
                    'title'   => 'Reservation Cancelled',
                    'message' => $reason . ' Property: ' . ($reservation->property->title ?? ''),
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        // Notify admins
        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        foreach ($admins as $admin) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'type'            => 'reservation_auto_cancelled',
                'data'            => [
                    'title'   => 'Reservation Auto-Cancelled',
                    'message' => 'Reservation #' . $reservation->id . ' for ' . ($reservation->property->title ?? '') . ' was auto-cancelled. Reason: ' . $reason,
                ],
                'priority' => 'normal',
                'is_read'  => false,
            ]);
        }

        AuditLog::log(
            'reservation_auto_cancelled',
            $reservation,
            "Reservation #{$reservation->id} auto-cancelled. Type: {$type}. Reason: {$reason}",
            ['status' => 'pending'],
            ['status' => 'cancelled', 'cancellation_type' => $type]
        );

        $this->line("  ✓ Cancelled Reservation #{$reservation->id} ({$type})");
    }
}
