<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_presentation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('logo', 500)->nullable();
            $table->string('banner', 500)->nullable();
            $table->string('banner2', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->enum('layout', ['grid', 'list', 'masonry'])->default('grid');
            $table->string('theme_color', 7)->nullable();
            $table->text('custom_css')->nullable();
            $table->string('seo_title', 255)->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->text('seo_keywords')->nullable();
            $table->timestamps();

            $table->unique('store_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_presentation');
    }
};
