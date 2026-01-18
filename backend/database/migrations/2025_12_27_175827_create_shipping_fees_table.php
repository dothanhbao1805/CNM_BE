<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_fees', function (Blueprint $table) {
            $table->id();

            $table->string('province_code')->nullable()->index();
            $table->string('province_name')->nullable()->index();
            $table->string('ward_code')->nullable()->index();
            $table->string('ward_name')->nullable()->index();
            $table->unsignedBigInteger('fee')->default(0); // cents
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_fees');
    }
};
