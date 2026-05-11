<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename contractors table to staff
        Schema::rename('contractors', 'staff');

        // 2. Rename contractor_id to staff_id in projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('contractor_id', 'staff_id');
        });

        // 3. Update role value in users table
        DB::table('users')->where('role', 'contractor')->update(['role' => 'staff']);
    }

    public function down(): void
    {
        Schema::rename('staff', 'contractors');

        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('staff_id', 'contractor_id');
        });

        DB::table('users')->where('role', 'staff')->update(['role' => 'contractor']);
    }
};
