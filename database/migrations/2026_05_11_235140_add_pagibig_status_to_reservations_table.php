<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('pagibig_status')->default('not_applied')->after('status');
            $table->string('pagibig_reference')->nullable()->after('pagibig_status');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['pagibig_status', 'pagibig_reference']);
        });
    }
};
