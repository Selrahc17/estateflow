<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('notes');
            $table->timestamp('data_wiped_at')->nullable()->after('cancelled_at');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->timestamp('data_wiped_at')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'data_wiped_at']);
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('data_wiped_at');
        });
    }
};
