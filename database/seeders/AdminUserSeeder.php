<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@lyrium.com'], [
            'name' => 'Admin Lyrium',
            'username' => 'admin',
            'nicename' => 'admin-lyrium',
            'is_admin' => true,
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $admin->assignRole('administrator');

        // Seller de prueba
        $seller = User::firstOrCreate(['email' => 'vendedor@lyrium.com'], [
            'name' => 'Vendedor Demo',
            'username' => 'vendedor_demo',
            'nicename' => 'vendedor-demo',
            'is_seller' => true,
            'phone' => '999888777',
            'document_type' => 'RUC',
            'document_number' => '20123456789',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $seller->assignRole('seller');

        // Crear tienda aprobada para el vendedor demo
        \App\Models\Store::firstOrCreate(['ruc' => '20123456789'], [
            'owner_id' => $seller->id,
            'trade_name' => 'BioTienda Demo',
            'corporate_email' => 'vendedor@lyrium.com',
            'slug' => 'biotienda-demo',
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }
}