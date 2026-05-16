<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('checklist_key')->nullable()->after('document_type');
            // submitted | approved | rejected | resubmitted | not_applicable
            $table->string('checklist_status')->nullable()->after('checklist_key');
            $table->text('rejection_reason')->nullable()->after('checklist_status');
            $table->text('not_applicable_reason')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['checklist_key', 'checklist_status', 'rejection_reason', 'not_applicable_reason']);
        });
    }
};
