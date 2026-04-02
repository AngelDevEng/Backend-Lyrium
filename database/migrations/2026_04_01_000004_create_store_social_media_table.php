<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_social_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->enum('platform', ['instagram', 'facebook', 'tiktok', 'twitter', 'linkedin', 'youtube', 'pinterest', 'website']);
            $table->string('username', 100)->nullable();
            $table->string('profile_url', 500)->nullable();
            $table->unsignedInteger('followers_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_primary')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('store_id');
            $table->unique(['store_id', 'platform'], 'unique_store_platform');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_social_media');
    }
};
