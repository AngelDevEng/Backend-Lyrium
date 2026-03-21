<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'angel.enginner08@gmail.com'], [
            'name' => 'Angel Engineer',
            'username' => 'angel_engineer',
            'nicename' => 'angel-engineer',
            'is_admin' => true,
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $admin->assignRole('administrator');

        $seller = User::firstOrCreate(['email' => 'angel.ipanaque.torre@gmail.com'], [
            'name' => 'Angel Ipanque',
            'username' => 'angel_ipanaque',
            'nicename' => 'angel-ipanaque',
            'is_seller' => true,
            'phone' => '999888777',
            'document_type' => 'RUC',
            'document_number' => '20123456789',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $seller->assignRole('seller');

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
