<?php

namespace App\Console\Commands;

use App\Models\EstateNotification;
use App\Models\PagibigAmortizationSchedule;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPagibigAmortizationReminders extends Command
{
    protected $signature   = 'pagibig:send-reminders';
    protected $description = 'Send reminders for overdue and upcoming Pag-IBIG amortization payments.';

    public function handle(): void
    {
        $this->syncStatuses();
        $this->remindOverdue();
        $this->remindUpcoming();
    }

    private function syncStatuses(): void
    {
        PagibigAmortizationSchedule::whereNotIn('status', ['paid'])
            ->get()
            ->each->syncStatus();
    }

    private function remindOverdue(): void
    {
        $schedules = PagibigAmortizationSchedule::with(['reservation.client.user', 'reservation.property'])
            ->where('status', 'overdue')
            ->get();

        foreach ($schedules as $schedule) {
            $clientUser = $schedule->reservation?->client?->user;
            if (!$clientUser) continue;

            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'pagibig_amortization_overdue',
                'data'            => [
                    'title'   => 'Overdue Pag-IBIG Amortization — Month ' . $schedule->month_number,
                    'message' => 'Your Pag-IBIG amortization for Month ' . $schedule->month_number . ' (₱' . number_format($schedule->amount_due, 2) . ') for ' . ($schedule->reservation->property->title ?? 'your property') . ' was due on ' . $schedule->due_date->format('M d, Y') . '. Please pay directly to Pag-IBIG (HDMF) immediately.',
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);

            if ($clientUser->email) {
                try {
                    Mail::send('emails.pagibig-amortization-overdue', ['schedule' => $schedule], function ($m) use ($clientUser, $schedule) {
                        $m->to($clientUser->email, $clientUser->name)
                          ->subject('Overdue Pag-IBIG Amortization — Month ' . $schedule->month_number);
                    });
                } catch (\Exception $e) {}
            }
        }

        $this->info("Pag-IBIG overdue reminders sent: {$schedules->count()}");
    }

    private function remindUpcoming(): void
    {
        $targetDate = now()->addDays(5)->toDateString();

        $schedules = PagibigAmortizationSchedule::with(['reservation.client.user', 'reservation.property'])
            ->where('status', 'upcoming')
            ->whereDate('due_date', $targetDate)
            ->get();

        foreach ($schedules as $schedule) {
            $clientUser = $schedule->reservation?->client?->user;
            if (!$clientUser) continue;

            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'pagibig_amortization_upcoming',
                'data'            => [
                    'title'   => 'Upcoming Pag-IBIG Amortization — Due in 5 Days',
                    'message' => 'Your Pag-IBIG amortization for Month ' . $schedule->month_number . ' (₱' . number_format($schedule->amount_due, 2) . ') for ' . ($schedule->reservation->property->title ?? 'your property') . ' is due on ' . $schedule->due_date->format('M d, Y') . '. Pay directly to Pag-IBIG (HDMF).',
                ],
                'priority' => 'normal',
                'is_read'  => false,
            ]);
        }

        $this->info("Pag-IBIG upcoming reminders sent: {$schedules->count()}");
    }
}
