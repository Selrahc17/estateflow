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
    public static function requirements(Reservation $reservation): array
    {
        $activeStatuses = [
            'pending', 'confirmed', 'reservation_paid',
            'pagibig_applied', 'pagibig_approved', 'pagibig_takeout',
            'pagibig_amortization', 'completed',
        ];

        if (!empty($reservation->document_checklist) && is_array($reservation->document_checklist)) {
            return array_map(function ($item) use ($activeStatuses) {
                return [
                    'type'         => $item['key'],
                    'label'        => $item['label'],
                    'required_for' => $activeStatuses,
                    'conditional'  => $item['conditional'] ?? false,
                ];
            }, $reservation->document_checklist);
        }

        return [
            ['type' => 'id',              'label' => 'Valid Government ID',            'required_for' => $activeStatuses, 'conditional' => false],
            ['type' => 'proof_of_income', 'label' => 'Proof of Income',                'required_for' => $activeStatuses, 'conditional' => false],
            ['type' => 'tin',             'label' => 'TIN / Tax Identification',       'required_for' => array_diff($activeStatuses, ['pending']), 'conditional' => false],
            ['type' => 'contract',        'label' => 'Contract to Sell',               'required_for' => array_diff($activeStatuses, ['pending']), 'conditional' => false],
            ['type' => 'deed_of_sale',    'label' => 'Deed of Sale',                   'required_for' => ['completed'], 'conditional' => false],
            ['type' => 'title',           'label' => 'Transfer Certificate of Title',  'required_for' => ['completed'], 'conditional' => false],
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

        foreach (self::requirements($reservation) as $req) {
            if (!in_array($status, $req['required_for'])) {
                continue;
            }

            $matches = $uploaded->filter(function ($doc) use ($req) {
                return $doc->document_type === $req['type'] || $doc->checklist_key === $req['type'];
            });

            $latest = $matches->sortByDesc('created_at')->first();
            $isNotApplicable = ($req['conditional'] ?? false) && $latest && $latest->checklist_status === 'not_applicable';
            $isRequired = !$isNotApplicable;
            $itemStatus = 'missing';

            if (!$latest) {
                $itemStatus = 'missing';
            } elseif ($isNotApplicable) {
                $itemStatus = 'not_applicable';
            } elseif ($latest->checklist_status) {
                $itemStatus = $latest->checklist_status;
            } elseif ($latest->isExpired()) {
                $itemStatus = 'expired';
            } elseif ($latest->isExpiringSoon()) {
                $itemStatus = 'expiring_soon';
            } elseif (!$latest->is_verified) {
                $itemStatus = 'pending_verification';
            } else {
                $itemStatus = 'verified';
            }

            if ($isRequired) {
                $required++;
                if ($itemStatus !== 'missing') {
                    $complete++;
                }
            }

            $results[] = [
                'type'         => $req['type'],
                'label'        => $req['label'],
                'status'       => $itemStatus,
                'conditional'  => $req['conditional'] ?? false,
                'document'     => $latest,
                'is_duplicate' => $matches->count() > 1,
                'count'        => $matches->count(),
            ];
        }

        $missing = $required - $complete;
        $score = $required > 0 ? round(($complete / $required) * 100) : 100;

        return [
            'results'                  => $results,
            'required'                 => $required,
            'complete'                 => $complete,
            'missing'                  => $missing,
            'score'                    => $score,
            'is_ready'                 => $score === 100,
            'is_completely_uploaded'   => $missing === 0 && $required > 0,
        ];
    }
}
