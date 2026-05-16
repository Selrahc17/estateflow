<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('pagibig_loan_status')->nullable()->after('pagibig_reference');
            // null | applied | approved | takeout | amortization
            $table->timestamp('pagibig_applied_at')->nullable()->after('pagibig_loan_status');
            $table->timestamp('pagibig_approved_at')->nullable()->after('pagibig_applied_at');
            $table->string('pagibig_loa_number')->nullable()->after('pagibig_approved_at');
            $table->timestamp('pagibig_takeout_at')->nullable()->after('pagibig_loa_number');
            $table->decimal('pagibig_takeout_amount', 15, 2)->nullable()->after('pagibig_takeout_at');
            $table->date('pagibig_amortization_start')->nullable()->after('pagibig_takeout_amount');
            $table->decimal('pagibig_monthly_amortization', 15, 2)->nullable()->after('pagibig_amortization_start');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'pagibig_loan_status',
                'pagibig_applied_at',
                'pagibig_approved_at',
                'pagibig_loa_number',
                'pagibig_takeout_at',
                'pagibig_takeout_amount',
                'pagibig_amortization_start',
                'pagibig_monthly_amortization',
            ]);
        });
    }
};
