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
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('phone_alt')->nullable();
            $table->text('address')->nullable();
            $table->string('license_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('type');
            $table->text('specialization')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
