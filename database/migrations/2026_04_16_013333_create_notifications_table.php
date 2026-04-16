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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('notifiable'); // Polymorphic relationship
            $table->string('type'); // e.g., 'reservation_reminder', 'payment_due', 'task_assigned'
            $table->text('data'); // JSON data
            $table->timestamp('read_at')->nullable();
            $table->string('priority')->default('normal'); // 'low', 'normal', 'high', 'urgent'
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
