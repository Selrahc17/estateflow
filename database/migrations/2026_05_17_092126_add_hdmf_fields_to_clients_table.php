<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('hdmf_mid')->nullable()->after('id_expiry');
            $table->decimal('monthly_income', 12, 2)->nullable()->after('hdmf_mid');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['hdmf_mid', 'monthly_income']);
        });
    }
};
