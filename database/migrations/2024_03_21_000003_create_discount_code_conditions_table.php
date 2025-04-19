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
        Schema::create('discount_code_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_code_id')->constrained('discount_codes')->onDelete('cascade');
            $table->string('type'); // e.g., 'category', 'product', 'customer_group', etc.
            $table->string('operator'); // e.g., 'in', 'not_in', 'equals', etc.
            $table->json('value'); // The actual condition value(s)
            $table->timestamps();

            // Index for faster lookups
            $table->index(['discount_code_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_code_conditions');
    }
}; 