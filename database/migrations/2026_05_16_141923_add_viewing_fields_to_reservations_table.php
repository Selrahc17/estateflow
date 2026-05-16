<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('viewing_status')->default('pending')->after('status');
            // pending → viewed → payment_uploaded → verified
            $table->string('proof_of_payment')->nullable()->after('viewing_status');
            $table->timestamp('viewed_at')->nullable()->after('proof_of_payment');
            $table->timestamp('payment_uploaded_at')->nullable()->after('viewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['viewing_status', 'proof_of_payment', 'viewed_at', 'payment_uploaded_at']);
        });
    }
};
