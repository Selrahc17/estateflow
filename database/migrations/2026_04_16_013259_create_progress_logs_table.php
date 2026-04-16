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
        Schema::create('progress_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->date('log_date');
            $table->text('description');
            $table->integer('completion_percentage')->default(0);
            $table->string('image_path')->nullable();
            $table->text('images')->nullable(); // JSON array of image paths
            $table->text('issues')->nullable();
            $table->text('weather_conditions')->nullable();
            $table->integer('workers_count')->nullable();
            $table->integer('hours_worked')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_logs');
    }
};
