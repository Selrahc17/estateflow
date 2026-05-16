<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->date('rf_deadline')->nullable()->after('reservation_fee');
            // Set by agent/admin after viewing — client has X days to pay RF
            $table->timestamp('rf_paid_at')->nullable()->after('rf_deadline');
            // Set by Finance when RF is verified
            $table->string('rf_or_number')->nullable()->after('rf_paid_at');
            // Official Receipt number issued by Finance for RF payment
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['rf_deadline', 'rf_paid_at', 'rf_or_number']);
        });
    }
};
