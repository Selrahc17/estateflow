<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminder;
use App\Models\EstateNotification;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    protected $signature   = 'reservations:send-reminders';
    protected $description = 'Send appointment reminder emails 3, 2, and 1 day before the reservation date.';

    public function handle(): void
    {
        $reminderDays = [3, 2, 1];

        foreach ($reminderDays as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            $reservations = Reservation::with(['client.user', 'property', 'agent'])
                ->whereDate('reservation_date', $targetDate)
                ->whereIn('viewing_status', ['pending'])
                ->whereNotIn('status', ['cancelled', 'expired', 'completed'])
                ->get();

            foreach ($reservations as $reservation) {
                $clientUser = $reservation->client?->user;
                if (!$clientUser?->email) continue;

                // Send reminder email
                try {
                    Mail::to($clientUser->email)->send(new AppointmentReminder($reservation, $days));
                    Log::info("Appointment reminder ({$days}d) sent to {$clientUser->email} for Reservation #{$reservation->id}");
                } catch (\Exception $e) {
                    Log::error("Failed to send reminder to {$clientUser->email}: " . $e->getMessage());
                }

                // In-app notification
                EstateNotification::create([
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $clientUser->id,
                    'type'            => 'appointment_reminder',
                    'data'            => [
                        'title'   => "Appointment in {$days} Day(s)",
                        'message' => "Reminder: Your viewing appointment for {$reservation->property->title} is on {$reservation->reservation_date->format('F j, Y')}.",
                    ],
                    'priority' => $days === 1 ? 'high' : 'normal',
                    'is_read'  => false,
                ]);
            }

            $this->info("Sent reminders for {$reservations->count()} reservation(s) with {$days} day(s) remaining.");
        }
    }
}
