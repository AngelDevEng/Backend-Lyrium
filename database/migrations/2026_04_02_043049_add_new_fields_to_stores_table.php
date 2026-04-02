<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('trade_name')->nullable()->after('description');
            $table->string('commercial_name')->nullable()->after('trade_name');
            $table->string('store_name')->nullable()->after('commercial_name');
            $table->string('logo')->nullable()->after('store_name');
            $table->string('banner')->nullable()->after('logo');
            $table->string('banner2')->nullable()->after('banner');
            $table->string('address')->nullable()->after('banner2');
            $table->string('phone', 20)->nullable()->after('address');
            $table->string('legal_representative_name')->nullable()->after('phone');
            $table->string('legal_representative_dni', 20)->nullable()->after('legal_representative_name');
            $table->string('legal_representative_photo')->nullable()->after('legal_representative_dni');
            $table->string('fiscal_address')->nullable()->after('legal_representative_photo');
            $table->string('account_bcp')->nullable()->after('fiscal_address');
            $table->string('cci')->nullable()->after('account_bcp');
            $table->json('bank_secondary')->nullable()->after('cci');
            $table->string('instagram')->nullable()->after('bank_secondary');
            $table->string('facebook')->nullable()->after('instagram');
            $table->string('tiktok')->nullable()->after('facebook');
            $table->string('whatsapp')->nullable()->after('tiktok');
            $table->string('youtube')->nullable()->after('whatsapp');
            $table->string('twitter')->nullable()->after('youtube');
            $table->string('linkedin')->nullable()->after('twitter');
            $table->string('website')->nullable()->after('linkedin');
            $table->text('policies')->nullable()->after('website');
            $table->json('gallery')->nullable()->after('policies');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'trade_name', 'commercial_name', 'store_name', 'logo', 'banner', 'banner2',
                'address', 'phone', 'legal_representative_name', 'legal_representative_dni',
                'legal_representative_photo', 'fiscal_address', 'account_bcp', 'cci',
                'bank_secondary', 'instagram', 'facebook', 'tiktok', 'whatsapp', 'youtube',
                'twitter', 'linkedin', 'website', 'policies', 'gallery',
            ]);
        });
    }
};
