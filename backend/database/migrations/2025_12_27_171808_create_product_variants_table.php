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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // Variant Attributes
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            
            // Stock & Pricing
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('price')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('product_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
