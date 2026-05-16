<?php

namespace App\Console\Commands;

use App\Mail\DataWiped;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class WipeExpiredClientData extends Command
{
    protected $signature   = 'retention:wipe';
    protected $description = 'Wipe personal data of clients whose cancelled reservations have passed the 7-day grace period.';

    public function handle(): void
    {
        $graceDays = config('retention.grace_period_days', 7);
        $cutoff    = now()->subDays($graceDays);

        // Find cancelled reservations past grace period that haven't been wiped yet
        $reservations = Reservation::where('status', 'cancelled')
            ->whereNotNull('cancelled_at')
            ->where('cancelled_at', '<=', $cutoff)
            ->whereNull('data_wiped_at')
            ->with(['client', 'payments', 'documents'])
            ->get();

        if ($reservations->isEmpty()) {
            $this->info('No expired records to wipe.');
            return;
        }

        $this->info("Found {$reservations->count()} reservation(s) to process...");

        foreach ($reservations as $reservation) {
            $this->wipeReservation($reservation);
        }

        $this->info('Data wipe completed.');
    }

    private function wipeReservation(Reservation $reservation): void
    {
        $client        = $reservation->client;
        $reservationId = $reservation->id;
        $clientRef     = $client ? "Client #{$client->id}" : 'Unknown Client';

        // 1. Delete uploaded documents from storage
        foreach ($reservation->documents as $document) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();
        }

        // 2. Delete payment proof images
        foreach ($reservation->payments as $payment) {
            if ($payment->proof_image && Storage::disk('public')->exists($payment->proof_image)) {
                Storage::disk('public')->delete($payment->proof_image);
            }
            // Anonymize payment record (keep for financial records but remove proof)
            $payment->update(['proof_image' => null]);
        }

        // 3. Check if client has other active reservations
        $otherActiveReservations = Reservation::where('client_id', $reservation->client_id)
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->where('id', '!=', $reservation->id)
            ->count();

        // 4. If no other active reservations, anonymize client personal data
        if ($client && $otherActiveReservations === 0 && !$client->data_wiped_at) {
            // Send data deletion email BEFORE wiping (while we still have their email)
            $emailAddress  = $client->email;
            $firstName     = $client->first_name;
            $propertyTitle = $reservation->property->title ?? 'your reserved property';
            $cancelledDate = $reservation->cancelled_at?->format('M d, Y') ?? 'N/A';
            $wipedDate     = now()->format('M d, Y');

            if ($emailAddress && !str_contains($emailAddress, '@removed.com')) {
                try {
                    Mail::to($emailAddress)->send(new DataWiped($firstName, $propertyTitle, $cancelledDate, $wipedDate));
                } catch (\Exception $e) {
                    $this->warn("  ⚠ Could not send data wipe email to {$emailAddress}: " . $e->getMessage());
                }
            }
            // Delete client uploaded documents
            $clientDocs = Document::where('documentable_type', Client::class)
                ->where('documentable_id', $client->id)
                ->get();

            foreach ($clientDocs as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }

            // Delete avatar
            if ($client->user && $client->user->avatar) {
                Storage::disk('public')->delete($client->user->avatar);
                $client->user->update(['avatar' => null]);
            }

            // Anonymize client personal data
            $client->update([
                'first_name' => 'Deleted',
                'last_name'  => 'User',
                'email'      => 'deleted_' . $client->id . '@removed.com',
                'phone'      => null,
                'phone_alt'  => null,
                'address'    => null,
                'id_type'    => null,
                'id_number'  => null,
                'id_expiry'  => null,
                'notes'      => null,
                'data_wiped_at' => now(),
            ]);
        }

        // 5. Mark reservation as wiped
        $reservation->update(['data_wiped_at' => now()]);

        // 6. Write audit log (no personal info)
        AuditLog::log(
            'data_wipe',
            null,
            "Reservation #{$reservationId} ({$clientRef}): personal data wiped after 7-day grace period following cancellation.",
        );

        $this->line("  ✓ Wiped data for Reservation #{$reservationId} ({$clientRef})");
    }
}
