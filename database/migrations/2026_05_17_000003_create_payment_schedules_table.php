<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->integer('installment_number');
            $table->date('due_date');
            $table->decimal('amount_due', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->storedAs('amount_due - amount_paid');
            $table->string('status')->default('upcoming');
            // upcoming | due | paid | partially_paid | overdue
            $table->string('receipt_number')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('proof_path')->nullable();
            $table->timestamp('proof_uploaded_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Add payment_schedule_id and receipt_number to payments
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('payment_schedule_id')->nullable()->constrained('payment_schedules')->onDelete('set null')->after('reservation_id');
            $table->string('receipt_number')->nullable()->after('reference_number');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['payment_schedule_id']);
            $table->dropColumn(['payment_schedule_id', 'receipt_number']);
        });

        Schema::dropIfExists('payment_schedules');
    }
};
