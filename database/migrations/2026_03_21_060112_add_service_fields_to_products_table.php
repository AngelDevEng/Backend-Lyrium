<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('service_location')->nullable()->after('file_size');
            $table->unsignedInteger('service_duration')->nullable()->after('service_location');
            $table->string('service_modality', 50)->nullable()->after('service_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['service_duration', 'service_modality', 'service_location']);
        });
    }
};
