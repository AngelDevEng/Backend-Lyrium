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
                'is_admin' => true,
                'phone' => '999000111',
                'departamento' => 'Lima',
                'provincia' => 'Lima',
                'distrito' => 'Miraflores',
                'admin_nombre' => 'Angel Engineer',
                'admin_dni' => '12345678',
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
                'is_seller' => true,
                'phone' => '999888777',
                'phone_2' => '988776655',
                'document_type' => 'RUC',
                'document_number' => '20123456789',
                'departamento' => 'Lima',
                'provincia' => 'Lima',
                'distrito' => 'San Juan de Lurigancho',
                'admin_nombre' => 'Angel Ipanque',
                'admin_dni' => '87654321',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $seller->assignRole('seller');

        $store = \App\Models\Store::updateOrCreate(
            ['ruc' => '20123456789'],
            [
                'owner_id' => $seller->id,
                'trade_name' => 'BioTienda Demo',
                'razon_social' => 'BioTienda Demo SAC',
                'nombre_comercial' => 'BioTienda Demo',
                'corporate_email' => 'vendedor@lyrium.com',
                'slug' => 'biotienda-demo',
                'phone' => '999888777',
                'status' => 'approved',
                'approved_at' => now(),
                'rep_legal_nombre' => 'Carlos García López',
                'rep_legal_dni' => '87654321',
                'experience_years' => 5,
                'tax_condition' => 'RUC',
                'direccion_fiscal' => 'Av. Arequipa 1234, Lima, Lima, Peru',
                'cuenta_bcp' => '123-456-789-012',
                'cci' => '002-123-456789012-34',
                'bank_secondary' => json_encode(['bank' => 'BBVA', 'account' => '001-234-567890123-45', 'cci' => '002-001-234567890123-45']),
                'store_name' => 'BioTienda Demo',
                'address' => 'Av. Arequipa 1234, Lima, Peru',
                'instagram' => 'biotiendademo',
                'facebook' => 'biotiendademo',
                'tiktok' => '@biotiendademo',
                'policies' => 'Política de devolución: Puede devolver productos en un plazo de 7 días desde la recepción.',
                'gallery' => json_encode(['gallery/img1.jpg', 'gallery/img2.jpg', 'gallery/img3.jpg']),
            ]
        );
    }
}
