<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('has_premium_insignia');
            $table->dropColumn('insignia_granted_at');
            $table->dropColumn('insignia_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('has_premium_insignia')->default(false)->after('status');
            $table->timestamp('insignia_granted_at')->nullable()->after('has_premium_insignia');
            $table->timestamp('insignia_requested_at')->nullable()->after('insignia_granted_at');
        });
    }
};
