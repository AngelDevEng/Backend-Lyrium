<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'angel.enginner08@gmail.com'],
            [
                'name' => 'Angel Engineer',
                'username' => 'angel_engineer',
                'nicename' => 'angel-engineer',
                'phone' => '999000111',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('administrator');

        $seller = User::updateOrCreate(
            ['email' => 'angel.ipanaque.torre@gmail.com'],
            [
                'name' => 'Angel Ipanque',
                'username' => 'angel_ipanaque',
                'nicename' => 'angel-ipanaque',
                'phone' => '999888777',
                'document_type' => 'RUC',
                'document_number' => '20123456789',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $seller->assignRole('seller');

        $store = \App\Models\Store::updateOrCreate(
            ['ruc' => '20123456789'],
            [
                'owner_id' => $seller->id,
                'trade_name_deprecated' => 'BioTienda Demo',
                'razon_social' => 'BioTienda Demo SAC',
                'nombre_comercial_deprecated' => 'BioTienda Demo',
                'corporate_email' => 'vendedor@lyrium.com',
                'slug' => 'biotienda-demo',
                'phone_deprecated' => '999888777',
                'status' => 'approved',
                'approved_at' => now(),
                'rep_legal_nombre_deprecated' => 'Carlos García López',
                'rep_legal_dni_deprecated' => '87654321',
                'experience_years' => 5,
                'tax_condition' => 'RUC',
                'direccion_fiscal_deprecated' => 'Av. Arequipa 1234, Lima, Lima, Peru',
                'cuenta_bcp_deprecated' => '123-456-789-012',
                'cci_deprecated' => '002-123-456789012-34',
                'bank_secondary_deprecated' => json_encode(['bank' => 'BBVA', 'account' => '001-234-567890123-45', 'cci' => '002-001-234567890123-45']),
                'store_name_deprecated' => 'BioTienda Demo',
                'address_deprecated' => 'Av. Arequipa 1234, Lima, Peru',
                'instagram_deprecated' => 'biotiendademo',
                'facebook_deprecated' => 'biotiendademo',
                'tiktok_deprecated' => '@biotiendademo',
                'policies_deprecated' => 'Política de devolución: Puede devolver productos en un plazo de 7 días desde la recepción.',
                'gallery_deprecated' => json_encode(['gallery/img1.jpg', 'gallery/img2.jpg', 'gallery/img3.jpg']),
            ]
        );

        \App\Models\StoreBranch::updateOrCreate(
            ['store_id' => $store->id, 'name' => 'Tienda Principal'],
            [
                'address' => 'Av. Arequipa 1234, Lima',
                'city' => 'Lima',
                'phone' => '01-456-7890',
                'hours' => 'Lun-Sab: 9:00 - 20:00',
                'is_principal' => true,
                'maps_url' => 'https://maps.google.com/?q=-12.046374,-77.042793',
                'is_active' => true,
            ]
        );

        \App\Models\StoreBranch::updateOrCreate(
            ['store_id' => $store->id, 'name' => 'Sucursal Miraflores'],
            [
                'address' => 'Av. Larco 456, Miraflores',
                'city' => 'Lima',
                'phone' => '01-234-5678',
                'hours' => 'Lun-Dom: 10:00 - 22:00',
                'is_principal' => false,
                'maps_url' => 'https://maps.google.com/?q=-12.120000,-77.030000',
                'is_active' => true,
            ]
        );

        $customer = User::updateOrCreate(
            ['email' => 'cliente@lyrium.com'],
            [
                'name' => 'Cliente Demo',
                'username' => 'cliente_demo',
                'nicename' => 'cliente-demo',
                'phone' => '999777666',
                'document_type' => 'DNI',
                'document_number' => '12345678',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $customer->assignRole('customer');

        $logistics = User::updateOrCreate(
            ['email' => 'logistica@lyrium.com'],
            [
                'name' => 'Logística Demo',
                'username' => 'logistica_demo',
                'nicename' => 'logistica-demo',
                'phone' => '999666555',
                'document_type' => 'DNI',
                'document_number' => '87654321',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $logistics->assignRole('logistics_operator');
    }
}
