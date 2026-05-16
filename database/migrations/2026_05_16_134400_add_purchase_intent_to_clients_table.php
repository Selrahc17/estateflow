<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('interested_property_id')->nullable()->constrained('properties')->onDelete('set null')->after('notes');
            $table->text('purchase_notes')->nullable()->after('interested_property_id');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['interested_property_id']);
            $table->dropColumn(['interested_property_id', 'purchase_notes']);
        });
    }
};
