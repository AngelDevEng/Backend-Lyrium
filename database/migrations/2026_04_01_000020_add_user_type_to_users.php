<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function ($table) {
            $table->enum('user_type', ['customer', 'seller', 'admin', 'delivery', 'supplier'])
                ->default('customer')
                ->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('user_type');
        });
    }
};
