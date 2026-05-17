<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Pag-IBIG loan vs equity reconciliation
            $table->decimal('pagibig_loan_amount', 12, 2)->nullable()->after('pagibig_monthly_amortization');
            $table->decimal('equity_amount', 12, 2)->nullable()->after('pagibig_loan_amount');

            // MRI / Fire Insurance
            $table->decimal('mri_premium', 12, 2)->nullable()->after('equity_amount');
            $table->string('mri_policy_number')->nullable()->after('mri_premium');
            $table->date('mri_expiry')->nullable()->after('mri_policy_number');
            $table->decimal('fire_insurance_premium', 12, 2)->nullable()->after('mri_expiry');
            $table->string('fire_insurance_policy_number')->nullable()->after('fire_insurance_premium');
            $table->date('fire_insurance_expiry')->nullable()->after('fire_insurance_policy_number');

            // Cancellation refund
            $table->decimal('refund_amount', 12, 2)->nullable()->after('cancellation_reason');
            $table->string('refund_status')->nullable()->after('refund_amount'); // pending, processed, waived
            $table->date('refund_processed_at')->nullable()->after('refund_status');
            $table->string('refund_reference')->nullable()->after('refund_processed_at');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'pagibig_loan_amount', 'equity_amount',
                'mri_premium', 'mri_policy_number', 'mri_expiry',
                'fire_insurance_premium', 'fire_insurance_policy_number', 'fire_insurance_expiry',
                'refund_amount', 'refund_status', 'refund_processed_at', 'refund_reference',
            ]);
        });
    }
};
