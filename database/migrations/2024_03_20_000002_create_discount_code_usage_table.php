<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_code_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_code_id')->constrained('discount_codes')->onDelete('cascade');
            $table->morphs('user');
            $table->morphs('order');
            $table->decimal('discount_amount', 10, 2);
            $table->decimal('original_amount', 10, 2);
            $table->decimal('final_amount', 10, 2);
            $table->json('applied_conditions')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_code_usage');
    }
}; 