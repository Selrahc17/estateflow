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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type');
            $table->text('description')->nullable();
            $table->string('unit');
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->string('currency')->default('PHP');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
