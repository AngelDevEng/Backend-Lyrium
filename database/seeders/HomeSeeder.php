<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Benefit;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;

final class HomeSeeder extends Seeder
{
    public function run(): void
    {
        $this->heroes();
        $this->banners();
        $this->brands();
        $this->benefits();
        $this->serviceCategories();
        $this->productCategories();
        $this->demoStore();
        $this->offerProducts();
        $this->newProducts();
    }

    private function heroes(): void
    {
        $heroes = [
            [
                'titulo' => 'Bienvenido a Lyrium',
                'descripcion' => 'Tu marketplace de productos saludables y de calidad',
                'imagen' => '/img/Inicio/1.png',
                'imagen_mobile' => '/img/Inicio/movil/1.webp',
                'seccion' => 'slider1',
                'position' => 1,
                'is_active' => true,
                'enlace' => '/productos',
            ],
            [
                'titulo' => 'Productos 100% Saludables',
                'descripcion' => 'Encuentra los mejores productos para tu bienestar',
                'imagen' => '/img/Inicio/2.png',
                'imagen_mobile' => '/img/Inicio/movil/2.webp',
                'seccion' => 'slider1',
                'position' => 2,
                'is_active' => true,
                'enlace' => '/categorias',
            ],
            [
                'titulo' => 'Cuida tu Salud',
                'descripcion' => 'Los mejores suplementos y alimentación natural',
                'imagen' => '/img/Inicio/3.png',
                'imagen_mobile' => '/img/Inicio/movil/3.webp',
                'seccion' => 'slider1',
                'position' => 3,
                'is_active' => true,
                'enlace' => '/suplementos-alimentacion',
            ],
            [
                'titulo' => 'Ofertas Especiales',
                'descripcion' => 'Descuentos exclusivos en productos selectedos',
                'imagen' => '/img/Inicio/4.png',
                'imagen_mobile' => '/img/Inicio/movil/4.webp',
                'seccion' => 'slider1',
                'position' => 4,
                'is_active' => true,
                'enlace' => '/ofertas',
            ],
            [
                'titulo' => 'Nuevos Productos',
                'descripcion' => 'Descubre las últimas incorporaciones',
                'imagen' => '/img/Inicio/5.png',
                'imagen_mobile' => '/img/Inicio/movil/5.webp',
                'seccion' => 'slider1',
                'position' => 5,
                'is_active' => true,
                'enlace' => '/novedades',
            ],
            [
                'titulo' => 'Compra Segura',
                'descripcion' => 'Tus compras protegidas y garantizadas',
                'imagen' => '/img/Inicio/6.png',
                'imagen_mobile' => '/img/Inicio/movil/6.webp',
                'seccion' => 'slider1',
                'position' => 6,
                'is_active' => true,
                'enlace' => '/productos',
            ],
        ];

        Banner::where('seccion', 'slider1')->delete();
        foreach ($heroes as $hero) {
            Banner::create($hero);
        }
    }

    private function banners(): void
    {
        Banner::whereIn('seccion', ['pequenos1', 'sliderMedianos1', 'pequenos2', 'sliderMedianos2', 'categoria_productos-digestion-saludable', 'categoria_productos-belleza'])->delete();

        Banner::create([
            'titulo' => 'Digestión Saludable',
            'descripcion' => 'Productos para tu digestión',
            'imagen' => '/img/banners/categorias/digestion-saludable.png',
            'seccion' => 'categoria_productos-digestion-saludable',
            'position' => 1,
            'is_active' => true,
            'enlace' => '/productos/digestion-saludable',
        ]);

        Banner::create([
            'titulo' => 'Belleza',
            'descripcion' => 'Productos de belleza y cuidado personal',
            'imagen' => '/img/banners/categorias/belleza.png',
            'seccion' => 'categoria_productos-belleza',
            'position' => 1,
            'is_active' => true,
            'enlace' => '/productos/belleza',
        ]);

        Banner::create([
            'titulo' => 'Servicios Médicos',
            'descripcion' => 'Servicios médicos profesionales',
            'imagen' => '/img/banners/categorias/servicios-medicos.png',
            'seccion' => 'categoria_servicios-medicos',
            'position' => 1,
            'is_active' => true,
            'enlace' => '/servicios/medicos',
        ]);

        Banner::create([
            'titulo' => 'Medicina Natural',
            'descripcion' => 'Servicios de medicina natural y alternativas',
            'imagen' => '/img/banners/categorias/servicios-medicina-natural.webp',
            'seccion' => 'categoria_servicios-naturales',
            'position' => 1,
            'is_active' => true,
            'enlace' => '/servicios/naturales',
        ]);

        $banners = [
            // Pequenos 1 (4 banners) - slider1 folder
            [
                'titulo' => 'Banner Pequeno 1',
                'descripcion' => '/ofertas',
                'imagen' => '/img/banners/slider1/banner_peq1.png',
                'seccion' => 'pequenos1',
                'position' => 1,
                'is_active' => true,
                'enlace' => '/ofertas',
            ],
            [
                'titulo' => 'Banner Pequeno 2',
                'descripcion' => '/suplementos',
                'imagen' => '/img/banners/slider1/banner_peq2.png',
                'seccion' => 'pequenos1',
                'position' => 2,
                'is_active' => true,
                'enlace' => '/suplementos-alimentacion',
            ],
            [
                'titulo' => 'Banner Pequeno 3',
                'descripcion' => '/belleza',
                'imagen' => '/img/banners/slider1/banner_peq3.png',
                'seccion' => 'pequenos1',
                'position' => 3,
                'is_active' => true,
                'enlace' => '/belleza',
            ],
            [
                'titulo' => 'Banner Pequeno 4',
                'descripcion' => '/mascotas',
                'imagen' => '/img/banners/slider1/banner_peq4.webp',
                'seccion' => 'pequenos1',
                'position' => 4,
                'is_active' => true,
                'enlace' => '/mascotas',
            ],
            // Slider Medianos 1 (4 banners) - slider1 folder
            [
                'titulo' => 'Banner Mediano 1',
                'descripcion' => '/belleza',
                'imagen' => '/img/banners/slider1/banner_med1.png',
                'seccion' => 'sliderMedianos1',
                'position' => 1,
                'is_active' => true,
                'enlace' => '/belleza',
            ],
            [
                'titulo' => 'Banner Mediano 2',
                'descripcion' => '/suplementos',
                'imagen' => '/img/banners/slider1/banner_med2.png',
                'seccion' => 'sliderMedianos1',
                'position' => 2,
                'is_active' => true,
                'enlace' => '/suplementos-alimentacion',
            ],
            [
                'titulo' => 'Banner Mediano 3',
                'descripcion' => '/salud',
                'imagen' => '/img/banners/slider1/banner_med3.png',
                'seccion' => 'sliderMedianos1',
                'position' => 3,
                'is_active' => true,
                'enlace' => '/salud-medicina',
            ],
            [
                'titulo' => 'Banner Mediano 4',
                'descripcion' => '/ofertas',
                'imagen' => '/img/banners/slider1/banner_med4.png',
                'seccion' => 'sliderMedianos1',
                'position' => 4,
                'is_active' => true,
                'enlace' => '/ofertas',
            ],
            // Pequenos 2 (4 banners) - slider2 folder
            [
                'titulo' => 'Banner Pequeno 5',
                'descripcion' => '/ofertas',
                'imagen' => '/img/banners/slider2/banner_peq1.webp',
                'seccion' => 'pequenos2',
                'position' => 1,
                'is_active' => true,
                'enlace' => '/ofertas',
            ],
            [
                'titulo' => 'Banner Pequeno 6',
                'descripcion' => '/nuevos',
                'imagen' => '/img/banners/slider2/banner_peq2.webp',
                'seccion' => 'pequenos2',
                'position' => 2,
                'is_active' => true,
                'enlace' => '/novedades',
            ],
            [
                'titulo' => 'Banner Pequeno 7',
                'descripcion' => '/belleza',
                'imagen' => '/img/banners/slider2/banner_peq3.webp',
                'seccion' => 'pequenos2',
                'position' => 3,
                'is_active' => true,
                'enlace' => '/belleza',
            ],
            [
                'titulo' => 'Banner Pequeno 8',
                'descripcion' => '/mascotas',
                'imagen' => '/img/banners/slider2/banner_peq4.webp',
                'seccion' => 'pequenos2',
                'position' => 4,
                'is_active' => true,
                'enlace' => '/mascotas',
            ],
            // Slider Medianos 2 (3 banners) - slider2 folder
            [
                'titulo' => 'Banner Mediano 5',
                'descripcion' => '/bienestar',
                'imagen' => '/img/banners/slider2/banner_med1.webp',
                'seccion' => 'sliderMedianos2',
                'position' => 1,
                'is_active' => true,
                'enlace' => '/bienestar',
            ],
            [
                'titulo' => 'Banner Mediano 6',
                'descripcion' => '/nuevos',
                'imagen' => '/img/banners/slider2/banner_med2.webp',
                'seccion' => 'sliderMedianos2',
                'position' => 2,
                'is_active' => true,
                'enlace' => '/novedades',
            ],
            [
                'titulo' => 'Banner Mediano 7',
                'descripcion' => '/servicios',
                'imagen' => '/img/banners/slider2/banner_med3.webp',
                'seccion' => 'sliderMedianos2',
                'position' => 3,
                'is_active' => true,
                'enlace' => '/servicios',
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }

    private function brands(): void
    {
        $brands = [
            ['name' => 'Natura Verde', 'slug' => 'natura-verde', 'logo' => '/img/brands/1.png', 'is_active' => true, 'position' => 1],
            ['name' => 'Eco Vida', 'slug' => 'eco-vida', 'logo' => '/img/brands/2.png', 'is_active' => true, 'position' => 2],
            ['name' => 'Bio Pure', 'slug' => 'bio-pure', 'logo' => '/img/brands/3.png', 'is_active' => true, 'position' => 3],
            ['name' => 'Green Fresh', 'slug' => 'green-fresh', 'logo' => '/img/brands/4.png', 'is_active' => true, 'position' => 4],
            ['name' => 'Vita Plus', 'slug' => 'vita-plus', 'logo' => '/img/brands/5.png', 'is_active' => true, 'position' => 5],
            ['name' => 'Pure Life', 'slug' => 'pure-life', 'logo' => '/img/brands/6.png', 'is_active' => true, 'position' => 6],
            ['name' => 'Healthy Mix', 'slug' => 'healthy-mix', 'logo' => '/img/brands/7.png', 'is_active' => true, 'position' => 7],
            ['name' => 'Organic Mix', 'slug' => 'organic-mix', 'logo' => '/img/brands/8.png', 'is_active' => true, 'position' => 8],
            ['name' => 'Bio Natur', 'slug' => 'bio-natur', 'logo' => '/img/brands/9.png', 'is_active' => true, 'position' => 9],
        ];

        Brand::truncate();
        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }

    private function benefits(): void
    {
        $benefits = [
            ['titulo' => 'Envío Gratis', 'descripcion' => 'En pedidos mayores a S/100', 'icono' => 'truck', 'is_active' => true, 'position' => 1],
            ['titulo' => '100% Orgánico', 'descripcion' => 'Productos certificados', 'icono' => 'leaf', 'is_active' => true, 'position' => 2],
            ['titulo' => 'Pago Seguro', 'descripcion' => 'Tus datos protegidos', 'icono' => 'shield', 'is_active' => true, 'position' => 3],
            ['titulo' => 'Soporte 24/7', 'descripcion' => 'Atención personalizada', 'icono' => 'headphones', 'is_active' => true, 'position' => 4],
            ['titulo' => 'Todo Salud', 'descripcion' => 'Tiendas saludables y ecoamigables', 'icono' => 'heart', 'is_active' => true, 'position' => 5],
            ['titulo' => 'Tiendas Selectas', 'descripcion' => 'Tiendas de calidad seleccionadas', 'icono' => 'storefront', 'is_active' => true, 'position' => 6],
        ];

        foreach ($benefits as $benefit) {
            Benefit::updateOrCreate(['titulo' => $benefit['titulo']], $benefit);
        }
    }

    private function serviceCategories(): void
    {
        $categories = [
            [
                'name' => 'Servicios Médicos',
                'slug' => 'servicios-medicos',
                'description' => 'Servicios de salud y medicina preventiva',
                'image' => '/img/categorias/servicios/medicos.webp',
                'sort_order' => 1,
                'type' => 'service',
            ],
            [
                'name' => 'Servicios de Belleza',
                'slug' => 'servicios-belleza',
                'description' => 'Tratamientos de belleza y cuidado personal',
                'image' => '/img/categorias/servicios/belleza.webp',
                'sort_order' => 2,
                'type' => 'service',
            ],
            [
                'name' => 'Servicios Ecológicos',
                'slug' => 'servicios-ecologicos',
                'description' => 'Servicios amigables con el medio ambiente',
                'image' => '/img/categorias/servicios/ecologicos.webp',
                'sort_order' => 3,
                'type' => 'service',
            ],
            [
                'name' => 'Servicios Deportivos',
                'slug' => 'servicios-deportivos',
                'description' => 'Entrenamiento y actividades físicas',
                'image' => '/img/categorias/servicios/deportes.webp',
                'sort_order' => 4,
                'type' => 'service',
            ],
            [
                'name' => 'Servicios para Mascotas',
                'slug' => 'servicios-mascotas',
                'description' => 'Cuidado y bienestar animal',
                'image' => '/img/categorias/servicios/mascotas.webp',
                'sort_order' => 5,
                'type' => 'service',
            ],
            [
                'name' => 'Servicios Naturales',
                'slug' => 'servicios-naturales',
                'description' => 'Terapias alternativas y naturales',
                'image' => '/img/categorias/servicios/naturales.webp',
                'sort_order' => 6,
                'type' => 'service',
            ],
            [
                'name' => 'Servicios Sociales',
                'slug' => 'servicios-sociales',
                'description' => 'Servicios comunitarios y sociales',
                'image' => '/img/categorias/servicios/sociales.webp',
                'sort_order' => 7,
                'type' => 'service',
            ],
        ];

        Category::where('type', 'service')->delete();
        foreach ($categories as $category) {
            Category::create($category);
        }
    }

    private function demoStore(): void
    {
        $store = Store::updateOrCreate(
            ['slug' => 'biotienda-demo'],
            [
                'owner_id' => 1,
                'trade_name' => 'BioTienda Demo',
                'razon_social' => 'BioTienda Demo SAC',
                'nombre_comercial' => 'BioTienda Demo',
                'corporate_email' => 'demo@lyrium.com',
                'ruc' => '20123456789',
                'phone' => '999999999',
                'status' => 'approved',
                'approved_at' => now(),
                'store_name' => 'Biotienda Demo',
                'address' => 'Av. Principal 123, Lima',
            ]
        );

        $this->command->info('Store demo ready: '.$store->store_name);
    }

    private function offerProducts(): void
    {
        $store = Store::where('slug', 'biotienda-demo')->first();
        if (! $store) {
            $this->command->warn('Store not found, skipping offer products');

            return;
        }

        $offers = [
            [
                'name' => 'Pack Suplementos Vitaminas',
                'slug' => 'pack-suplementos-vitaminas',
                'description' => 'Pack completo de vitaminas y minerales para toda la familia. Incluye multivitaminas, vitamina C, zinc y omega-3.',
                'short_description' => 'Pack multivitaminas familiar',
                'price' => 89.00,
                'regular_price' => 129.00,
                'stock' => 50,
                'weight' => 0.5,
                'sticker' => 'oferta',
                'status' => 'approved',
                'type' => 'physical',
                'sku' => 'BIO-OFERTA-001',
                'rating_promedio' => 4.7,
                'rating_count' => 89,
                'image' => '/img/productos/digestion/Digestión-saludable.png',
            ],
            [
                'name' => 'Kit Cuidado Facial Orgánico',
                'slug' => 'kit-cuidado-facial-organico',
                'description' => 'Kit completo de cuidado facial con productos orgánicos: limpiador, tónico, serum y crema hidratante.',
                'short_description' => 'Kit facial orgánico 4 pasos',
                'price' => 75.00,
                'regular_price' => 99.00,
                'stock' => 30,
                'weight' => 0.3,
                'sticker' => 'oferta',
                'status' => 'approved',
                'type' => 'physical',
                'sku' => 'BIO-OFERTA-002',
                'rating_promedio' => 4.5,
                'rating_count' => 56,
                'image' => '/img/productos/belleza/banner-principal-cremas.png',
            ],
            [
                'name' => 'Aceites Esenciales Pack Premium',
                'slug' => 'aceites-esenciales-pack-premium',
                'description' => 'Pack de 6 aceites esenciales puros: lavanda, árbol de té, eucalipto, menta, rosa y jazmín.',
                'short_description' => 'Pack 6 aceites esenciales',
                'price' => 120.00,
                'regular_price' => 180.00,
                'stock' => 25,
                'weight' => 0.6,
                'sticker' => 'oferta',
                'status' => 'approved',
                'type' => 'physical',
                'sku' => 'BIO-OFERTA-003',
                'rating_promedio' => 4.8,
                'rating_count' => 112,
                'image' => '/img/productos/belleza/espuma-limpiadora1.png',
            ],
            [
                'name' => 'Consulta Nutricional Online',
                'slug' => 'consulta-nutricional-online',
                'description' => 'Consulta online con nutricionista certified. Plan alimenticio personalizado y seguimiento mensual.',
                'short_description' => 'Consulta nutricional online',
                'price' => 59.00,
                'regular_price' => 89.00,
                'stock' => 20,
                'sticker' => 'oferta',
                'status' => 'approved',
                'type' => 'service',
                'sku' => 'SERV-OFERTA-001',
                'rating_promedio' => 4.9,
                'rating_count' => 78,
                'service_duration' => 60,
                'service_modality' => 'online',
                'service_location' => 'Video llamada',
                'image' => '/img/productos/servicios-medicos/Diagnostico unipolar.png',
            ],
            [
                'name' => 'Sesión de Masoterapia',
                'slug' => 'sesion-masoterapia',
                'description' => 'Sesión de masoterapia relajante con aceites esenciales. Alivia estrés y tensión muscular.',
                'short_description' => 'Sesión masoterapia 60 min',
                'price' => 65.00,
                'regular_price' => 90.00,
                'stock' => 15,
                'sticker' => 'oferta',
                'status' => 'approved',
                'type' => 'service',
                'sku' => 'SERV-OFERTA-002',
                'rating_promedio' => 4.7,
                'rating_count' => 45,
                'service_duration' => 60,
                'service_modality' => 'presencial',
                'service_location' => 'Spa Natural',
                'image' => '/img/productos/servicios-medicos/masajes-corporales.png',
            ],
        ];

        foreach ($offers as $data) {
            $existing = Product::where('slug', $data['slug'])->first();
            if ($existing) {
                continue;
            }

            $data['store_id'] = $store->id;
            $data['discount_percentage'] = $data['regular_price'] > $data['price']
                ? round((($data['regular_price'] - $data['price']) / $data['regular_price']) * 100, 2)
                : 0;

            Product::create($data);
        }

        $this->command->info('Offer products created: '.count($offers));
    }

    private function newProducts(): void
    {
        $store = Store::where('slug', 'biotienda-demo')->first();
        if (! $store) {
            return;
        }

        $newProducts = [
            [
                'name' => 'Colageno Marino Premium',
                'slug' => 'colageno-marino-premium',
                'description' => 'Colágeno marino de alta absorción para piel, cabello y articulaciones. Fórmula mejorada con vitamina C.',
                'short_description' => 'Colágeno marino con vitamina C',
                'price' => 78.00,
                'regular_price' => 78.00,
                'stock' => 80,
                'weight' => 0.2,
                'sticker' => 'nuevo',
                'status' => 'approved',
                'type' => 'physical',
                'sku' => 'BIO-NUEVO-001',
                'rating_promedio' => 4.6,
                'rating_count' => 23,
                'image' => '/img/productos/digestion/colageno-marino.png',
                'created_at' => now()->subDays(5),
            ],
            [
                'name' => 'Creatina Monohidrato',
                'slug' => 'creatina-monohidrato',
                'description' => 'Creatina monohidrato pura micronizada. Incrementa fuerza y rendimiento deportivo.',
                'short_description' => 'Creatina micronizada 300g',
                'price' => 45.00,
                'regular_price' => 45.00,
                'stock' => 100,
                'weight' => 0.3,
                'sticker' => 'nuevo',
                'status' => 'approved',
                'type' => 'physical',
                'sku' => 'BIO-NUEVO-002',
                'rating_promedio' => 4.8,
                'rating_count' => 67,
                'image' => '/img/productos/nuevos/creatina.webp',
                'created_at' => now()->subDays(3),
            ],
            [
                'name' => 'Melatonina Natural 5mg',
                'slug' => 'melatonina-natural-5mg',
                'description' => 'Melatonina natural para ayudarte a dormir mejor. Fórmula de liberación rápida.',
                'short_description' => 'Melatonina 5mg para dormir',
                'price' => 22.00,
                'regular_price' => 22.00,
                'stock' => 150,
                'weight' => 0.05,
                'sticker' => 'nuevo',
                'status' => 'approved',
                'type' => 'physical',
                'sku' => 'BIO-NUEVO-003',
                'rating_promedio' => 4.4,
                'rating_count' => 34,
                'image' => '/img/productos/nuevos/melatonina.webp',
                'created_at' => now()->subDays(7),
            ],
            [
                'name' => 'Pre-Entrenamiento Natural',
                'slug' => 'pre-entrenamiento-natural',
                'description' => 'Pre-entrenamiento 100% natural con cafeína verde, taurina y vitaminas del grupo B.',
                'short_description' => 'Pre-workout natural sin azúcar',
                'price' => 55.00,
                'regular_price' => 55.00,
                'stock' => 60,
                'weight' => 0.3,
                'sticker' => 'nuevo',
                'status' => 'approved',
                'type' => 'physical',
                'sku' => 'BIO-NUEVO-004',
                'rating_promedio' => 4.5,
                'rating_count' => 28,
                'image' => '/img/productos/nuevos/intra-entrenamiento.webp',
                'created_at' => now()->subDays(2),
            ],
        ];

        foreach ($newProducts as $data) {
            $createdAt = $data['created_at'] ?? now();
            unset($data['created_at']);

            $existing = Product::where('slug', $data['slug'])->first();
            if ($existing) {
                continue;
            }

            $data['store_id'] = $store->id;
            $data['discount_percentage'] = 0;

            Product::create($data);
        }

        $this->command->info('New products created: '.count($newProducts));
    }

    private function productCategories(): void
    {
        $store = Store::where('slug', 'biotienda-demo')->first();
        if (! $store) {
            return;
        }

        $productsByCategory = [
            'productos-digestion-saludable' => [
                [
                    'name' => 'Probióticos Flora Intestinal',
                    'slug' => 'probioticos-flora-intestinal',
                    'description' => 'Suplemento de probióticos con 50 mil millones de UFC para salud intestinal.',
                    'short_description' => 'Probióticos 50B UFC',
                    'price' => 58.00,
                    'regular_price' => 68.00,
                    'stock' => 120,
                    'weight' => 0.08,
                    'sticker' => 'organic',
                    'status' => 'approved',
                    'type' => 'physical',
                    'sku' => 'BIO-DIG-001',
                    'rating_promedio' => 4.3,
                    'rating_count' => 91,
                    'image' => '/img/productos/digestion/haba-tostada.png',
                ],
                [
                    'name' => 'Melatonina 10mg',
                    'slug' => 'melatonina-10mg',
                    'description' => 'Melatonina de liberación prolongada para un sueño reparador.',
                    'short_description' => 'Melatonina 10mg',
                    'price' => 25.00,
                    'regular_price' => 30.00,
                    'stock' => 80,
                    'weight' => 0.05,
                    'sticker' => 'natural',
                    'status' => 'approved',
                    'type' => 'physical',
                    'sku' => 'BIO-DIG-002',
                    'rating_promedio' => 4.5,
                    'rating_count' => 45,
                    'image' => '/img/productos/digestion/melatonin-10mg.png',
                ],
            ],
            'productos-belleza' => [
                [
                    'name' => 'Crema Hidratante Aloe Vera',
                    'slug' => 'crema-hidratante-aloe-vera',
                    'description' => 'Crema facial hidratante con aloe vera orgánico y aceite de jojoba.',
                    'short_description' => 'Crema hidratante orgánica',
                    'price' => 42.00,
                    'regular_price' => 48.00,
                    'stock' => 88,
                    'weight' => 0.1,
                    'sticker' => 'natural',
                    'status' => 'approved',
                    'type' => 'physical',
                    'sku' => 'BIO-BEL-001',
                    'rating_promedio' => 4.5,
                    'rating_count' => 132,
                    'image' => '/img/productos/belleza/banner-principal-cremas.png',
                ],
                [
                    'name' => 'Espuma Limpiadora Facial',
                    'slug' => 'espuma-limpiadora-facial',
                    'description' => 'Espuma limpiadora suave con extracto de té verde y manzanilla.',
                    'short_description' => 'Espuma limpiadora herbal',
                    'price' => 28.00,
                    'regular_price' => 35.00,
                    'stock' => 60,
                    'weight' => 0.15,
                    'sticker' => 'oferta',
                    'status' => 'approved',
                    'type' => 'physical',
                    'sku' => 'BIO-BEL-002',
                    'rating_promedio' => 4.6,
                    'rating_count' => 78,
                    'image' => '/img/productos/belleza/espuma-limpiadora1.png',
                ],
            ],
            'servicios-medicos' => [
                [
                    'name' => 'Ecografía Obstétrica 4D',
                    'slug' => 'ecografia-obstetrica-4d',
                    'description' => 'Ecografía obstétrica de alta resolución con imágenes 4D del bebé.',
                    'short_description' => 'Ecografía obstétrica 4D',
                    'price' => 120.00,
                    'regular_price' => 150.00,
                    'stock' => 10,
                    'sticker' => 'oferta',
                    'status' => 'approved',
                    'type' => 'service',
                    'sku' => 'SER-MED-001',
                    'rating_promedio' => 4.9,
                    'rating_count' => 45,
                    'service_duration' => 30,
                    'service_modality' => 'presencial',
                    'service_location' => 'Consultorio médico',
                    'image' => '/img/productos/servicios-medicos/banner-principal-medicos.png',
                ],
                [
                    'name' => 'Profilaxis Dental con Fluor',
                    'slug' => 'profilaxis-dental-fluor',
                    'description' => 'Limpieza dental profesional con ultrasonido y fluoruro.',
                    'short_description' => 'Limpieza dental con fluor',
                    'price' => 80.00,
                    'regular_price' => 100.00,
                    'stock' => 20,
                    'sticker' => 'oferta',
                    'status' => 'approved',
                    'type' => 'service',
                    'sku' => 'SER-MED-002',
                    'rating_promedio' => 4.8,
                    'rating_count' => 89,
                    'service_duration' => 45,
                    'service_modality' => 'presencial',
                    'service_location' => 'Clínica dental',
                    'image' => '/img/productos/servicios-medicos/blanqueamiento-dental.png',
                ],
            ],
            'servicios-naturales' => [
                [
                    'name' => 'Consulta Naturopatía',
                    'slug' => 'consulta-naturopatia',
                    'description' => 'Consulta de naturopatía y medicina alternativa personalizada.',
                    'short_description' => 'Consulta naturopatía',
                    'price' => 90.00,
                    'regular_price' => 120.00,
                    'stock' => 15,
                    'sticker' => 'nuevo',
                    'status' => 'approved',
                    'type' => 'service',
                    'sku' => 'SER-NAT-001',
                    'rating_promedio' => 4.7,
                    'rating_count' => 34,
                    'service_duration' => 60,
                    'service_modality' => 'presencial',
                    'service_location' => 'Centro naturista',
                    'image' => '/img/categorias/servicios/naturales.webp',
                ],
            ],
        ];

        foreach ($productsByCategory as $categorySlug => $products) {
            $category = Category::where('slug', $categorySlug)->first();
            if (! $category) {
                continue;
            }

            foreach ($products as $data) {
                $existing = Product::where('slug', $data['slug'])->first();
                if ($existing) {
                    $existing->categories()->syncWithoutDetaching([$category->id]);

                    continue;
                }

                $data['store_id'] = $store->id;
                $data['discount_percentage'] = $data['regular_price'] > $data['price']
                    ? round((($data['regular_price'] - $data['price']) / $data['regular_price']) * 100, 2)
                    : 0;

                $product = Product::create($data);
                $product->categories()->attach($category->id);
            }
        }

        $this->command->info('Category products created');
    }
}
