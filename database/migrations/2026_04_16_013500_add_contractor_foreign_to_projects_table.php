<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreign('contractor_id')->references('id')->on('contractors')->onDelete('set null');
        });

        Schema::table('resources', function (Blueprint $table) {
            $table->foreign('supplier_id')->references('id')->on('contractors')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['contractor_id']);
        });

        Schema::table('resources', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });
    }
};
