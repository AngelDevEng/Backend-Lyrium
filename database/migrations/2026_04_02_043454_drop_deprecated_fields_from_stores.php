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
                'trade_name_deprecated',
                'nombre_comercial_deprecated',
                'rep_legal_nombre_deprecated',
                'rep_legal_dni_deprecated',
                'rep_legal_foto_deprecated',
                'direccion_fiscal_deprecated',
                'cuenta_bcp_deprecated',
                'cci_deprecated',
                'bank_secondary_deprecated',
                'logo_deprecated',
                'banner_deprecated',
                'banner2_deprecated',
                'store_name_deprecated',
                'address_deprecated',
                'phone_deprecated',
                'instagram_deprecated',
                'facebook_deprecated',
                'tiktok_deprecated',
                'whatsapp_deprecated',
                'youtube_deprecated',
                'twitter_deprecated',
                'linkedin_deprecated',
                'website_deprecated',
                'policies_deprecated',
                'gallery_deprecated',
            ];

            foreach ($columns as $column) {
                $table->dropColumn($column);
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('trade_name_deprecated')->nullable();
            $table->string('nombre_comercial_deprecated')->nullable();
            $table->string('rep_legal_nombre_deprecated')->nullable();
            $table->string('rep_legal_dni_deprecated')->nullable();
            $table->string('rep_legal_foto_deprecated')->nullable();
            $table->string('direccion_fiscal_deprecated')->nullable();
            $table->string('cuenta_bcp_deprecated')->nullable();
            $table->string('cci_deprecated')->nullable();
            $table->json('bank_secondary_deprecated')->nullable();
            $table->string('logo_deprecated')->nullable();
            $table->string('banner_deprecated')->nullable();
            $table->string('banner2_deprecated')->nullable();
            $table->string('store_name_deprecated')->nullable();
            $table->string('address_deprecated')->nullable();
            $table->string('phone_deprecated')->nullable();
            $table->string('instagram_deprecated')->nullable();
            $table->string('facebook_deprecated')->nullable();
            $table->string('tiktok_deprecated')->nullable();
            $table->string('whatsapp_deprecated')->nullable();
            $table->string('youtube_deprecated')->nullable();
            $table->string('twitter_deprecated')->nullable();
            $table->string('linkedin_deprecated')->nullable();
            $table->string('website_deprecated')->nullable();
            $table->text('policies_deprecated')->nullable();
            $table->text('gallery_deprecated')->nullable();
        });
    }
};
