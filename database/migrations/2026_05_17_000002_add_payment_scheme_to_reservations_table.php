<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'employment_type'))
                $table->string('employment_type')->nullable()->after('payment_scheme');
            if (!Schema::hasColumn('reservations', 'coborrower_name'))
                $table->string('coborrower_name')->nullable()->after('employment_type');
            if (!Schema::hasColumn('reservations', 'coborrower_relationship'))
                $table->string('coborrower_relationship')->nullable()->after('coborrower_name');
            if (!Schema::hasColumn('reservations', 'coborrower_contact'))
                $table->string('coborrower_contact')->nullable()->after('coborrower_relationship');
            if (!Schema::hasColumn('reservations', 'document_checklist'))
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
