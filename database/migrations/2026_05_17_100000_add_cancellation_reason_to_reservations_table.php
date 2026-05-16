<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('cancellation_reason')->nullable()->after('cancelled_at');
            $table->string('cancellation_type')->nullable()->after('cancellation_reason');
            // cancellation_type: 'manual_admin' | 'auto_no_action' | 'auto_rf_expired'
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'cancellation_type']);
        });
    }
};
