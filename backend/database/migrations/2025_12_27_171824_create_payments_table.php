<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->onDelete('cascade');

            $table->string('transaction_no')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('card_type')->nullable();
            $table->timestamp('pay_date')->nullable();
            $table->string('response_code')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
