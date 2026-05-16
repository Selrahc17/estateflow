<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // payment_scheme already exists — skip it

            $table->string('employment_type')->nullable()->after('payment_scheme');
            $table->string('coborrower_name')->nullable()->after('employment_type');
            $table->string('coborrower_relationship')->nullable()->after('coborrower_name');
            $table->string('coborrower_contact')->nullable()->after('coborrower_relationship');
            $table->json('document_checklist')->nullable()->after('coborrower_contact');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'employment_type',
                'coborrower_name',
                'coborrower_relationship',
                'coborrower_contact',
                'document_checklist',
            ]);
        });
    }
};
