<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');

            $table->foreignId('order_id')
                  ->nullable()
                  ->constrained('orders')
                  ->onDelete('set null');

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->tinyInteger('rating')->unsigned()->default(5);
            $table->text('content')->nullable();
            $table->json('images')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
