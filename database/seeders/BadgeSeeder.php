<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

final class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'type' => 'verified',
                'name' => 'Verified',
                'description' => 'Tienda verificada con RUC y DNI validados',
                'icon' => 'verified',
            ],
            [
                'type' => 'top_seller',
                'name' => 'Top Seller',
                'description' => 'Vendedor destacado con calificación >= 4.5 y más de 50 ventas',
                'icon' => 'star',
            ],
            [
                'type' => 'express_shipping',
                'name' => 'Express Shipping',
                'description' => '20+ pedidos entregados en menos de 12 horas',
                'icon' => 'bolt',
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['type' => $badge['type']],
                $badge
            );
        }
    }
}
