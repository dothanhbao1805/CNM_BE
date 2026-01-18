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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            
            // Basic Information
            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            
            // Pricing
            $table->unsignedInteger('price');
            $table->unsignedInteger('compare_price')->nullable();
            
            // Product Details
            $table->string('material')->nullable();
            $table->text('care_instructions')->nullable();
            $table->string('dress_style')->nullable();
            
            // Status
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('slug');
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
