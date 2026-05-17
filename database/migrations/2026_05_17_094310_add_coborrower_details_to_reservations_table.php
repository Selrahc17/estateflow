<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->decimal('coborrower_monthly_income', 12, 2)->nullable()->after('coborrower_contact');
            $table->string('coborrower_id_type')->nullable()->after('coborrower_monthly_income');
            $table->string('coborrower_id_number')->nullable()->after('coborrower_id_type');
            $table->date('coborrower_id_expiry')->nullable()->after('coborrower_id_number');
            $table->string('coborrower_hdmf_mid')->nullable()->after('coborrower_id_expiry');
            $table->string('coborrower_employment_type')->nullable()->after('coborrower_hdmf_mid');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'coborrower_monthly_income', 'coborrower_id_type',
                'coborrower_id_number', 'coborrower_id_expiry',
                'coborrower_hdmf_mid', 'coborrower_employment_type',
            ]);
        });
    }
};
