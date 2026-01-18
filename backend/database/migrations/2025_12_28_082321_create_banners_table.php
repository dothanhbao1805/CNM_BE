<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            $table->string('title')->nullable();
            $table->string('image_url');
            $table->string('link_to')->nullable();
            $table->string('position')->nullable(); // ví dụ: homepage_top, sidebar, footer...
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Index gợi ý (tối ưu truy vấn)
            $table->index('is_active');
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
