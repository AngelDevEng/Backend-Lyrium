<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_legal_representatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('nombre', 255);
            $table->string('dni', 8);
            $table->string('foto_url')->nullable();
            $table->text('direccion_fiscal')->nullable();
            $table->boolean('is_current')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->index('store_id');
            $table->unique(['store_id', 'is_current'], 'unique_current_rep');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_legal_representatives');
    }
};
