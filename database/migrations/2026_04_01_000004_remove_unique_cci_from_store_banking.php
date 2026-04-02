<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_banking', function ($table) {
            $table->string('cci', 20)->nullable()->unique(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('store_banking', function ($table) {
            $table->string('cci', 20)->nullable()->unique()->change();
        });
    }
};
