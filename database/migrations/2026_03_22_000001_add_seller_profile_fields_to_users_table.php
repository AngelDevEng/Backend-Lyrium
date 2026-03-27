<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('departamento', 50)->nullable()->after('document_number');
            $table->string('provincia', 50)->nullable()->after('departamento');
            $table->string('distrito', 50)->nullable()->after('provincia');
            $table->string('admin_nombre', 255)->nullable()->after('distrito');
            $table->string('admin_dni', 20)->nullable()->after('admin_nombre');
            $table->string('phone_2', 20)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'departamento',
                'provincia',
                'distrito',
                'admin_nombre',
                'admin_dni',
                'phone_2',
            ]);
        });
    }
};
