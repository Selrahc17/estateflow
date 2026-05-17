<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->decimal('property_price', 12, 2);
            $table->decimal('commission_rate', 5, 2)->default(5.00); // percentage
            $table->decimal('commission_amount', 12, 2);
            $table->string('status')->default('pending'); // pending, approved, paid, cancelled
            $table->date('approved_at')->nullable();
            $table->date('paid_at')->nullable();
            $table->string('or_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_commissions');
    }
};
