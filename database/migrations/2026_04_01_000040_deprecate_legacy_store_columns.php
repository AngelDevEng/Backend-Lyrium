<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table): void {
            $table->renameColumn('store_name', 'store_name_deprecated');
            $table->renameColumn('trade_name', 'trade_name_deprecated');
            $table->renameColumn('nombre_comercial', 'nombre_comercial_deprecated');
            $table->renameColumn('address', 'address_deprecated');
            $table->renameColumn('phone', 'phone_deprecated');
            $table->renameColumn('whatsapp', 'whatsapp_deprecated');
            $table->renameColumn('cuenta_bcp', 'cuenta_bcp_deprecated');
            $table->renameColumn('cci', 'cci_deprecated');
            $table->renameColumn('bank_secondary', 'bank_secondary_deprecated');
            $table->renameColumn('instagram', 'instagram_deprecated');
            $table->renameColumn('facebook', 'facebook_deprecated');
            $table->renameColumn('tiktok', 'tiktok_deprecated');
            $table->renameColumn('youtube', 'youtube_deprecated');
            $table->renameColumn('twitter', 'twitter_deprecated');
            $table->renameColumn('linkedin', 'linkedin_deprecated');
            $table->renameColumn('website', 'website_deprecated');
            $table->renameColumn('logo', 'logo_deprecated');
            $table->renameColumn('banner', 'banner_deprecated');
            $table->renameColumn('banner2', 'banner2_deprecated');
            $table->renameColumn('policies', 'policies_deprecated');
            $table->renameColumn('gallery', 'gallery_deprecated');
            $table->renameColumn('rep_legal_nombre', 'rep_legal_nombre_deprecated');
            $table->renameColumn('rep_legal_dni', 'rep_legal_dni_deprecated');
            $table->renameColumn('rep_legal_foto', 'rep_legal_foto_deprecated');
            $table->renameColumn('direccion_fiscal', 'direccion_fiscal_deprecated');

            $table->string('trade_name_deprecated')->nullable()->change();
            $table->string('nombre_comercial_deprecated')->nullable()->change();
            $table->string('store_name_deprecated')->nullable()->change();
            $table->string('address_deprecated')->nullable()->change();
            $table->string('phone_deprecated')->nullable()->change();
            $table->string('whatsapp_deprecated')->nullable()->change();
            $table->string('cuenta_bcp_deprecated')->nullable()->change();
            $table->string('cci_deprecated')->nullable()->change();
            $table->json('bank_secondary_deprecated')->nullable()->change();
            $table->string('instagram_deprecated')->nullable()->change();
            $table->string('facebook_deprecated')->nullable()->change();
            $table->string('tiktok_deprecated')->nullable()->change();
            $table->string('youtube_deprecated')->nullable()->change();
            $table->string('twitter_deprecated')->nullable()->change();
            $table->string('linkedin_deprecated')->nullable()->change();
            $table->string('website_deprecated')->nullable()->change();
            $table->string('logo_deprecated')->nullable()->change();
            $table->string('banner_deprecated')->nullable()->change();
            $table->string('banner2_deprecated')->nullable()->change();
            $table->text('policies_deprecated')->nullable()->change();
            $table->json('gallery_deprecated')->nullable()->change();
            $table->string('rep_legal_nombre_deprecated')->nullable()->change();
            $table->string('rep_legal_dni_deprecated')->nullable()->change();
            $table->string('rep_legal_foto_deprecated')->nullable()->change();
            $table->string('direccion_fiscal_deprecated')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table): void {
            $table->renameColumn('store_name_deprecated', 'store_name');
            $table->renameColumn('trade_name_deprecated', 'trade_name');
            $table->renameColumn('nombre_comercial_deprecated', 'nombre_comercial');
            $table->renameColumn('address_deprecated', 'address');
            $table->renameColumn('phone_deprecated', 'phone');
            $table->renameColumn('whatsapp_deprecated', 'whatsapp');
            $table->renameColumn('cuenta_bcp_deprecated', 'cuenta_bcp');
            $table->renameColumn('cci_deprecated', 'cci');
            $table->renameColumn('bank_secondary_deprecated', 'bank_secondary');
            $table->renameColumn('instagram_deprecated', 'instagram');
            $table->renameColumn('facebook_deprecated', 'facebook');
            $table->renameColumn('tiktok_deprecated', 'tiktok');
            $table->renameColumn('youtube_deprecated', 'youtube');
            $table->renameColumn('twitter_deprecated', 'twitter');
            $table->renameColumn('linkedin_deprecated', 'linkedin');
            $table->renameColumn('website_deprecated', 'website');
            $table->renameColumn('logo_deprecated', 'logo');
            $table->renameColumn('banner_deprecated', 'banner');
            $table->renameColumn('banner2_deprecated', 'banner2');
            $table->renameColumn('policies_deprecated', 'policies');
            $table->renameColumn('gallery_deprecated', 'gallery');
            $table->renameColumn('rep_legal_nombre_deprecated', 'rep_legal_nombre');
            $table->renameColumn('rep_legal_dni_deprecated', 'rep_legal_dni');
            $table->renameColumn('rep_legal_foto_deprecated', 'rep_legal_foto');
            $table->renameColumn('direccion_fiscal_deprecated', 'direccion_fiscal');
        });
    }
};
