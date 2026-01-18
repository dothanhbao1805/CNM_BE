<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('house_number')->nullable();
            $table->string('province')->nullable();
            $table->string('ward')->nullable();
            $table->text('note')->nullable();

            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('order_status')->nullable();

            $table->unsignedBigInteger('subtotal')->default(0);     // cents
            $table->unsignedBigInteger('discount')->default(0);     // cents
            $table->unsignedBigInteger('delivery_fee')->default(0);// cents
            $table->unsignedBigInteger('total')->default(0);        // cents
            
            $table->softDeletes(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
