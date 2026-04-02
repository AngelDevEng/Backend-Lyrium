<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->enum('location_type', ['warehouse', 'store', 'office', 'pickup_point']);
            $table->text('address');
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country', 2)->default('PE');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_pickup_available')->default(false);
            $table->json('operating_hours')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->timestamps();

            $table->index('store_id');
            $table->index('location_type');
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_locations');
    }
};
