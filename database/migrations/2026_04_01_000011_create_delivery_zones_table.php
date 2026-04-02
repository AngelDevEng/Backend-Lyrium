<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_partner_id')->constrained('delivery_partners')->cascadeOnDelete();
            $table->string('district', 100);
            $table->string('city', 100)->nullable();
            $table->decimal('base_fee', 8, 2)->default(0.00);
            $table->decimal('per_km_fee', 8, 2)->default(0.00);
            $table->decimal('min_order_amount', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('delivery_partner_id');
            $table->unique(['delivery_partner_id', 'district'], 'unique_partner_district');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
