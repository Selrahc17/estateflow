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
        Schema::create('ai_predictions', function (Blueprint $table) {
            $table->id();
            $table->morphs('predictable'); // Polymorphic relationship (property, project, etc.)
            $table->string('prediction_type'); // 'price_prediction', 'market_analysis', 'progress_analysis', 'recommendation'
            $table->decimal('predicted_value', 15, 2)->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable(); // 0-100
            $table->text('prediction_data'); // JSON data with detailed predictions
            $table->text('input_features'); // JSON data of input features used
            $table->string('model_version')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_predictions');
    }
};
