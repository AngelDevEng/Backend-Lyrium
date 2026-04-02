<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_banking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('bank_code', 10);
            $table->string('bank_name', 50);
            $table->enum('account_type', ['savings', 'checking', 'cci']);
            $table->string('account_number', 30)->nullable();
            $table->string('cci', 20)->nullable()->unique();
            $table->enum('currency', ['PEN', 'USD', 'EUR'])->default('PEN');
            $table->string('holder_name', 255)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->enum('purpose', ['sales', 'purchases', 'payroll', 'taxes'])->default('sales');
            $table->timestamps();

            $table->index('store_id');
            $table->index('bank_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_banking');
    }
};
