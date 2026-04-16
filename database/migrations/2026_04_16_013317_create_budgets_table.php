<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('category'); // e.g., "materials", "labor", "equipment", "permits", "contingency"
            $table->text('description')->nullable();
            $table->decimal('estimated_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->string('currency')->default('PHP');
            $table->date('budget_date');
            $table->enum('status', ['planned', 'approved', 'in_progress', 'completed', 'over_budget'])->default('planned');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
