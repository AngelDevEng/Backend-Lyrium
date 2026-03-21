<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    private const BASE_URL = 'https://lyriumbiomarketplace.com/wp-content/uploads/2025/08';

    private const PRODUCT_CATEGORIES = [
        [
            'name' => 'Belleza',
            'slug' => 'productos-belleza',
            'description' => 'Productos de belleza y cuidado personal',
            'image' => '/storage/img/categorias/productos/belleza.webp',
            'sort_order' => 1,
            'type' => 'product',
        ],
        [
            'name' => 'Bienestar Físico y Deporte',
            'slug' => 'productos-bienestar-fisico',
            'description' => 'Productos para bienestar físico y deporte',
            'image' => '/storage/img/categorias/productos/bienestar-fisico-deporte.png',
            'sort_order' => 2,
            'type' => 'product',
        ],
        [
            'name' => 'Digestión Saludable',
            'slug' => 'productos-digestion-saludable',
            'description' => 'Productos para digestión saludable',
            'image' => '/storage/img/categorias/productos/digestion-saludable.png',
            'sort_order' => 3,
            'type' => 'product',
        ],
        [
            'name' => 'Equipos y Dispositivos Médicos',
            'slug' => 'productos-equipos-medicos',
            'description' => 'Equipos y dispositivos médicos',
            'image' => '/storage/img/categorias/productos/equipos y dispositivos-medicos.png',
            'sort_order' => 4,
            'type' => 'product',
        ],
        [
            'name' => 'Mascotas',
            'slug' => 'productos-mascotas',
            'description' => 'Productos para mascotas',
            'image' => '/storage/img/categorias/productos/mascotas.png',
            'sort_order' => 5,
            'type' => 'product',
        ],
        [
            'name' => 'Protección, Limpieza y Desinfección',
            'slug' => 'productos-limpieza',
            'description' => 'Productos de protección, limpieza y desinfección',
            'image' => '/storage/img/categorias/productos/protecion-limpieza-desinfencion.png',
            'sort_order' => 6,
            'type' => 'product',
        ],
        [
            'name' => 'Suplementos Vitamínicos',
            'slug' => 'productos-suplementos',
            'description' => 'Suplementos vitamínicos y nutricionales',
            'image' => '/storage/img/categorias/productos/sumplementos-vitaminicos.png',
            'sort_order' => 7,
            'type' => 'product',
        ],
    ];

    private const SERVICE_CATEGORIES = [
        [
            'name' => 'Belleza',
            'slug' => 'servicios-belleza',
            'description' => 'Servicios de belleza y cuidado personal',
            'image' => '/storage/img/categorias/servicios/belleza.webp',
            'sort_order' => 1,
            'type' => 'service',
        ],
        [
            'name' => 'Deportes',
            'slug' => 'servicios-deportes',
            'description' => 'Servicios deportivos y actividad física',
            'image' => '/storage/img/categorias/servicios/deportes.webp',
            'sort_order' => 2,
            'type' => 'service',
        ],
        [
            'name' => 'Ecológicos',
            'slug' => 'servicios-ecologicos',
            'description' => 'Productos y servicios ecológicos',
            'image' => '/storage/img/categorias/servicios/ecologicos.webp',
            'sort_order' => 3,
            'type' => 'service',
        ],
        [
            'name' => 'Mascotas',
            'slug' => 'servicios-mascotas',
            'description' => 'Servicios para mascotas',
            'image' => '/storage/img/categorias/servicios/mascotas.webp',
            'sort_order' => 4,
            'type' => 'service',
        ],
        [
            'name' => 'Médicos',
            'slug' => 'servicios-medicos',
            'description' => 'Servicios médicos profesionales',
            'image' => '/storage/img/categorias/servicios/medicos.webp',
            'sort_order' => 5,
            'type' => 'service',
        ],
        [
            'name' => 'Naturales',
            'slug' => 'servicios-naturales',
            'description' => 'Productos y servicios naturales',
            'image' => '/storage/img/categorias/servicios/naturales.webp',
            'sort_order' => 6,
            'type' => 'service',
        ],
        [
            'name' => 'Sociales',
            'slug' => 'servicios-sociales',
            'description' => 'Servicios sociales y comunitarios',
            'image' => '/storage/img/categorias/servicios/sociales.webp',
            'sort_order' => 7,
            'type' => 'service',
        ],
    ];

    public function run(): void
    {
        Category::where('type', 'service')->delete();
        Category::where('type', 'product')->delete();
        $this->createCategories(self::SERVICE_CATEGORIES);
        $this->createCategories(self::PRODUCT_CATEGORIES);
    }

    private function createCategories(array $categories, ?int $parentId = null): void
    {
        foreach ($categories as $cat) {
            $children = $cat['children'] ?? [];
            unset($cat['children']);

            $cat['parent_id'] = $parentId;
            $cat['type'] = $cat['type'] ?? 'product';

            $category = Category::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );

            if (!empty($children)) {
                $this->createCategories($children, $category->id);
            }
        }
    }
}
