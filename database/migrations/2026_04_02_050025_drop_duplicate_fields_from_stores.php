<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $columns = [
                'phone',
                'whatsapp',
                'instagram',
                'facebook',
                'tiktok',
                'youtube',
                'twitter',
                'linkedin',
                'website',
                'legal_representative_name',
                'legal_representative_dni',
                'legal_representative_photo',
                'fiscal_address',
                'account_bcp',
                'cci',
                'bank_secondary',
                'address',
            ];

            foreach ($columns as $column) {
                $table->dropColumn($column);
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('youtube')->nullable();
            $table->string('twitter')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('website')->nullable();
            $table->string('legal_representative_name')->nullable();
            $table->string('legal_representative_dni', 20)->nullable();
            $table->string('legal_representative_photo')->nullable();
            $table->string('fiscal_address')->nullable();
            $table->string('account_bcp')->nullable();
            $table->string('cci')->nullable();
            $table->json('bank_secondary')->nullable();
            $table->string('address')->nullable();
        });
    }
};
