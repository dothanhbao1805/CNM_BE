<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->text('description')->nullable();

            // type lưu string (percent/fixed), cast vào Enum trong model
            $table->string('type')->nullable();

            // money in cents
            $table->unsignedBigInteger('value')->default(0);
            $table->unsignedBigInteger('min_order_value')->default(0);
            $table->unsignedBigInteger('max_discount')->default(0);

            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used')->default(0);

            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
