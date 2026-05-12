<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Document;
use App\Models\Client;

class DocumentCheckerService
{
    /**
     * Required document types per reservation status.
     */
    public static function requirements(): array
    {
        return [
            ['type' => 'id',              'label' => 'Valid Government ID',            'required_for' => ['pending', 'confirmed', 'completed']],
            ['type' => 'proof_of_income', 'label' => 'Proof of Income',                'required_for' => ['pending', 'confirmed', 'completed']],
            ['type' => 'tin',             'label' => 'TIN / Tax Identification',       'required_for' => ['confirmed', 'completed']],
            ['type' => 'contract',        'label' => 'Contract to Sell',               'required_for' => ['confirmed', 'completed']],
            ['type' => 'deed_of_sale',    'label' => 'Deed of Sale',                   'required_for' => ['completed']],
            ['type' => 'title',           'label' => 'Transfer Certificate of Title',  'required_for' => ['completed']],
        ];
    }

    /**
     * Run the document check for a reservation.
     */
    public static function check(Reservation $reservation): array
    {
        $status        = $reservation->status ?? 'pending';
        $clientId      = $reservation->client_id;
        $reservationId = $reservation->id;

        $uploaded = Document::where(function ($q) use ($clientId, $reservationId) {
            $q->where(function ($q2) use ($clientId) {
                $q2->where('documentable_type', Client::class)
                   ->where('documentable_id', $clientId);
            })->orWhere(function ($q2) use ($reservationId) {
                $q2->where('documentable_type', Reservation::class)
                   ->where('documentable_id', $reservationId);
            });
        })->get();

        $results  = [];
        $required = 0;
        $complete = 0;

        foreach (self::requirements() as $req) {
            if (!in_array($status, $req['required_for'])) {
                continue;
            }

            $required++;
            $matches = $uploaded->where('document_type', $req['type']);
            $latest  = $matches->sortByDesc('created_at')->first();

            if (!$latest) {
                $itemStatus = 'missing';
            } elseif ($latest->isExpired()) {
                $itemStatus = 'expired';
            } elseif ($latest->isExpiringSoon()) {
                $itemStatus = 'expiring_soon';
                $complete++;
            } elseif (!$latest->is_verified) {
                $itemStatus = 'pending_verification';
                $complete++;
            } else {
                $itemStatus = 'verified';
                $complete++;
            }

            $results[] = [
                'type'         => $req['type'],
                'label'        => $req['label'],
                'status'       => $itemStatus,
                'document'     => $latest,
                'is_duplicate' => $matches->count() > 1,
                'count'        => $matches->count(),
            ];
        }

        $score = $required > 0 ? round(($complete / $required) * 100) : 100;

        return [
            'results'  => $results,
            'required' => $required,
            'complete' => $complete,
            'missing'  => $required - $complete,
            'score'    => $score,
            'is_ready' => $score === 100,
        ];
    }
}
