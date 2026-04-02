<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('company_name', 255)->nullable();
            $table->enum('partner_type', ['individual', 'company', 'platform']);
            $table->string('ruc', 11)->nullable()->unique();
            $table->string('dni', 8)->nullable();
            $table->string('phone', 20);
            $table->string('email', 255);
            $table->enum('vehicle_type', ['bike', 'motorcycle', 'car', 'van', 'truck'])->nullable();
            $table->string('license_plate', 10)->nullable();
            $table->string('license_number', 20)->nullable();
            $table->string('status')->default('pending');
            $table->decimal('rating_average', 3, 2)->default(0.00);
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('total_deliveries')->default(0);
            $table->decimal('commission_rate', 5, 2)->default(15.00);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('partner_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_partners');
    }
};
