<?php

namespace App\Services;

use App\Models\Reservation;

class DocumentChecklistService
{
    // Base docs for Cash / Bank Transfer
    private const CASH_BANK = [
        ['key' => 'valid_id_2',      'label' => '2 Valid Government IDs',              'conditional' => false],
        ['key' => 'proof_of_income', 'label' => 'Proof of Income (Payslip or ITR)',    'conditional' => false],
        ['key' => 'marriage_cert',   'label' => 'Marriage Certificate (if applicable)','conditional' => true],
        ['key' => 'birth_cert',      'label' => 'Birth Certificate',                   'conditional' => false],
    ];

    private const PAGIBIG_BASE = [
        ['key' => 'valid_id_specimen', 'label' => 'Valid ID with 3 Specimen Signature',          'conditional' => false],
        ['key' => 'picture_1x1',       'label' => '1x1 Picture (8 pcs)',                          'conditional' => false],
        ['key' => 'marriage_cert',     'label' => 'Marriage Certificate (if applicable)',          'conditional' => true],
        ['key' => 'birth_cert',        'label' => 'Birth Certificate',                             'conditional' => false],
        ['key' => 'pagibig_contrib',   'label' => 'Updated Pag-IBIG Contribution (24 months)',    'conditional' => false],
        ['key' => 'virtual_pagibig',   'label' => 'Virtual Pag-IBIG Account / Loyalty Card',      'conditional' => false],
    ];

    private const BY_EMPLOYMENT = [
        'locally_employed' => [
            ['key' => 'coe_payslip',     'label' => 'COE with Compensation / Payslip'],
            ['key' => 'hla',             'label' => 'Housing Loan Application (HLA) Details'],
        ],
        'locally_employed_coborrower' => [
            ['key' => 'coe_payslip',     'label' => 'COE with Compensation / Payslip'],
            ['key' => 'coborrower_hla',  'label' => 'Co-Borrower Housing Loan Application (HLA) Details'],
            ['key' => 'virtual_pagibig', 'label' => 'Virtual Pag-IBIG Account / Loyalty Card'],
        ],
        'self_employed' => [
            ['key' => 'ctc_itr_permits', 'label' => 'CTC ITR, Business Permits, DTI'],
            ['key' => 'hla',             'label' => 'Housing Loan Application (HLA) Details'],
        ],
        'ofw' => [
            ['key' => 'emp_contract',    'label' => 'Employment Contract / COE with Compensation'],
            ['key' => 'hla',             'label' => 'Housing Loan Application (HLA) Details'],
            ['key' => 'arrival_departure','label' => 'Arrival and Departure'],
            ['key' => 'spa',             'label' => 'Special Power of Attorney (SPA)'],
            ['key' => 'working_permit',  'label' => 'Working Permit'],
        ],
        'ofw_coborrower' => [
            ['key' => 'coe_payslip',     'label' => 'COE with Compensation / Payslip'],
            ['key' => 'coborrower_hla',  'label' => 'Co-Borrower HLA Details'],
        ],
    ];

    public static function generate(Reservation $reservation): array
    {
        if ($reservation->payment_scheme === 'cash_bank') {
            $items = self::CASH_BANK;
        } else {
            // Pag-IBIG: base + employment-specific docs
            $extra = self::BY_EMPLOYMENT[$reservation->employment_type] ?? [];

            // For coborrower types, virtual_pagibig is in the extra list — remove from base to avoid duplicate
            $base = self::PAGIBIG_BASE;
            if (in_array($reservation->employment_type, ['locally_employed_coborrower', 'ofw_coborrower'])) {
                $base = array_filter($base, fn($d) => $d['key'] !== 'virtual_pagibig');
            }

            $items = array_merge(array_values($base), $extra);
        }

        // Add uploaded/verified status to each item
        return array_map(fn($item) => array_merge($item, [
            'uploaded'         => false,
            'verified'         => false,
            'rejected'         => false,
            'rejection_reason' => null,
            'not_applicable'   => false,
            'na_reason'        => null,
            'file_path'        => null,
        ]), $items);
    }
}
