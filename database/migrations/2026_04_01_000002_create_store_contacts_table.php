<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->enum('contact_type', ['phone', 'email', 'whatsapp', 'landline']);
            $table->enum('contact_role', ['primary', 'secondary', 'admin', 'support', 'finance']);
            $table->string('value', 100);
            $table->string('label', 100)->nullable();
            $table->string('owner_name', 255)->nullable();
            $table->string('owner_dni', 8)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('store_id');
            $table->index(['contact_type', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_contacts');
    }
};
