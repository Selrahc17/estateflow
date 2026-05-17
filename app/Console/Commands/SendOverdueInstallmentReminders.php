<?php

namespace App\Console\Commands;

use App\Models\EstateNotification;
use App\Models\PaymentSchedule;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOverdueInstallmentReminders extends Command
{
    protected $signature   = 'installments:send-reminders';
    protected $description = 'Send reminders for overdue and upcoming equity installments.';

    public function handle(): void
    {
        $this->remindOverdue();
        $this->remindUpcoming();
    }

    private function remindOverdue(): void
    {
        $schedules = PaymentSchedule::with(['reservation.client.user', 'reservation.property'])
            ->where('status', 'overdue')
            ->get();

        foreach ($schedules as $schedule) {
            $clientUser = $schedule->reservation?->client?->user;
            if (!$clientUser) continue;

            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'installment_overdue',
                'data'            => [
                    'title'   => 'Overdue Installment — Installment #' . $schedule->installment_number,
                    'message' => 'Your equity installment #' . $schedule->installment_number . ' of ₱' . number_format($schedule->amount_due, 2) . ' for ' . ($schedule->reservation->property->title ?? 'your property') . ' was due on ' . $schedule->due_date->format('M d, Y') . ' and is now overdue. Please settle immediately.',
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);

            if ($clientUser->email) {
                try {
                    Mail::send('emails.installment-overdue', ['schedule' => $schedule], function ($m) use ($clientUser, $schedule) {
                        $m->to($clientUser->email, $clientUser->name)
                          ->subject('Overdue Installment — Installment #' . $schedule->installment_number);
                    });
                } catch (\Exception $e) {}
            }
        }

        $this->info("Overdue reminders sent: {$schedules->count()}");
    }

    private function remindUpcoming(): void
    {
        // Remind 3 days before due date
        $targetDate = now()->addDays(3)->toDateString();

        $schedules = PaymentSchedule::with(['reservation.client.user', 'reservation.property'])
            ->where('status', 'upcoming')
            ->whereDate('due_date', $targetDate)
            ->get();

        foreach ($schedules as $schedule) {
            $clientUser = $schedule->reservation?->client?->user;
            if (!$clientUser) continue;

            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'installment_upcoming',
                'data'            => [
                    'title'   => 'Upcoming Installment — Due in 3 Days',
                    'message' => 'Installment #' . $schedule->installment_number . ' of ₱' . number_format($schedule->amount_due, 2) . ' for ' . ($schedule->reservation->property->title ?? 'your property') . ' is due on ' . $schedule->due_date->format('M d, Y') . '.',
                ],
                'priority' => 'normal',
                'is_read'  => false,
            ]);
        }

        $this->info("Upcoming reminders sent: {$schedules->count()}");
    }
}
