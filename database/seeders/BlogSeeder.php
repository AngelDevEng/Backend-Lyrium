<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogPodcast;
use App\Models\BlogPost;
use App\Models\BlogVideo;
use Illuminate\Database\Seeder;

final class BlogSeeder extends Seeder
{
    private const BLOG_IMAGES = [
        'laptop.jpg',
        'joven-sentado.jpg',
        'entrevista_doctora-scaled.webp',
        'chica-sentada.jpg',
        'ICON.png',
        'blog-teclas.jpg',
        'Fondos_BioBlog-6.webp',
        'Familia-en-picnic-scaled.webp',
    ];

    public function run(): void
    {
        $this->categories();
        $this->posts();
        $this->podcasts();
        $this->videos();
    }

    private function categories(): void
    {
        $categories = [
            [
                'name' => 'Salud Alimentaria',
                'slug' => 'salud-alimentaria',
                'description' => 'Consejos y tips sobre alimentación saludable y productos bio',
                'image' => '/storage/img/blog/laptop.jpg',
                'sort_order' => 1,
            ],
            [
                'name' => 'Salud Ambiental',
                'slug' => 'salud-ambiental',
                'description' => 'Información sobre productos ecológicos y cuidado del medio ambiente',
                'image' => '/storage/img/blog/Fondos_BioBlog-6.webp',
                'sort_order' => 2,
            ],
            [
                'name' => 'Salud Emocional',
                'slug' => 'salud-emocional',
                'description' => 'Tips para el bienestar emocional y mental',
                'image' => '/storage/img/blog/joven-sentado.jpg',
                'sort_order' => 3,
            ],
            [
                'name' => 'Salud Espiritual',
                'slug' => 'salud-espiritual',
                'description' => 'Prácticas y consejos para el bienestar espiritual',
                'image' => '/storage/img/blog/chica-sentada.jpg',
                'sort_order' => 4,
            ],
            [
                'name' => 'Salud Familiar',
                'slug' => 'salud-familiar',
                'description' => 'Consejos para mantener sana a toda la familia',
                'image' => '/storage/img/blog/Familia-en-picnic-scaled.webp',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            BlogCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }

        $this->command->info('Blog categories created: '.count($categories));
    }

    private function posts(): void
    {
        $categories = BlogCategory::pluck('id', 'slug');
        $images = self::BLOG_IMAGES;

        $posts = [
            [
                'category' => 'salud-alimentaria',
                'title' => '5 Superfoods que debes incluir en tu dieta',
                'slug' => '5-superfoods-dieta-saludable',
                'excerpt' => 'Descubre los superfoods que pueden transformar tu salud y energía diaria.',
                'content' => '<p>Los superfoods son alimentos ricos en nutrientes que pueden mejorar significativamente tu salud. Aquí te presentamos los 5 más importantes que debes incluir en tu dieta diaria.</p><h2>1. Semillas de Chía</h2><p>Ricas en omega-3, fibra y antioxidantes, las semillas de chía son un alimento versátil que puedes agregar a smoothies, bowls y recetas.</p><h2>2. Espirulina</h2><p>Este alga verdeazulada es una fuente completa de proteína vegetal y contiene más betacaroteno que las zanahorias.</p><h2>3. Bayas de Açaí</h2><p>Potentes antioxidantes que protegen tus células del envejecimiento prematuro.</p><h2>4. Cúrcuma</h2><p>Con su compuesto activo curcumina, es conocida por sus propiedades antiinflamatorias.</p><h2>5. Té Verde Matcha</h2><p>Rico en antioxidantes y proporciona energía sostenida sin los efectos del café.</p>',
                'featured_image' => '/storage/img/blog/entrevista_doctora-scaled.webp',
                'author_name' => 'Lic. María González',
                'is_published' => true,
                'is_featured' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'category' => 'salud-alimentaria',
                'title' => 'Cómo leer etiquetas de productos orgánicos',
                'slug' => 'como-leer-etiquetas-productos-organicos',
                'excerpt' => 'Aprende a identificar los verdaderos productos orgánicos y evitar engaños en el supermercado.',
                'content' => '<p>Cuando compras productos orgánicos, es importante saber leer las etiquetas correctamente. Te enseñamos qué buscar y qué evitar.</p><h2>Certificaciones a buscar</h2><p>Busca sellos de certificación orgánica como USDA Organic, EU Organic o certificaciones locales.</p><h2>Ingredientes a evitar</h2><p>Cuidado con productos que dicen "natural" pero contienen ingredientes artificiales.</p>',
                'featured_image' => '/storage/img/blog/laptop.jpg',
                'author_name' => 'Nutricionista Ana Martínez',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now()->subDays(5),
            ],
            [
                'category' => 'salud-ambiental',
                'title' => 'Guía para empezar una huerta urbana',
                'slug' => 'guia-huerta-urbana',
                'excerpt' => 'Paso a paso para crear tu propio huerto urbano y cultivar alimentos frescos en casa.',
                'content' => '<p>Cultivar tus propios alimentos es una experiencia gratificante y sostenible. Aquí te damos una guía completa para empezar.</p><h2>Elección del espacio</h2><p>Puedes usar balcones, terrazas o incluso espacios interiores con luz natural.</p><h2>Plantas recomendadas para principiantes</h2><p>Hierbas como albahaca, menta y tomates cherry son ideales para comenzar.</p>',
                'featured_image' => '/storage/img/blog/Fondos_BioBlog-6.webp',
                'author_name' => 'Ing. Carlos Ruiz',
                'is_published' => true,
                'is_featured' => true,
                'published_at' => now()->subDays(1),
            ],
            [
                'category' => 'salud-ambiental',
                'title' => 'Plastic-free: reduce tu consumo de plástico',
                'slug' => 'plastic-free-reduce-plastico',
                'excerpt' => 'Aprende a reducir el plástico en tu vida diaria con estos consejos prácticos.',
                'content' => '<p>El plástico es uno de los mayores contaminantes de nuestro tiempo. Aquí te compartimos formas de reducir su uso.</p><h2>En la cocina</h2><p>Usa recipientes de vidrio, bolsas de tela y evita plásticos de un solo uso.</p>',
                'featured_image' => '/storage/img/blog/blog-teclas.jpg',
                'author_name' => 'Eco-activista Laura Díaz',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now()->subDays(7),
            ],
            [
                'category' => 'salud-emocional',
                'title' => 'Meditación para principiantes',
                'slug' => 'meditacion-principiantes',
                'excerpt' => 'Una guía simple para empezar a meditar y mejorar tu bienestar emocional.',
                'content' => '<p>La meditación es una práctica milenaria que puede transformar tu vida. Te mostramos cómo empezar.</p><h2>Beneficios de la meditación</h2><p>Reduce el estrés, mejora la concentración y aumenta la sensación de bienestar.</p>',
                'featured_image' => '/storage/img/blog/joven-sentado.jpg',
                'author_name' => 'Coach espiritual Pedro Sánchez',
                'is_published' => true,
                'is_featured' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'category' => 'salud-emocional',
                'title' => 'Cómo manejar el estrés laboral',
                'slug' => 'manejar-estres-laboral',
                'excerpt' => 'Técnicas efectivas para manejar el estrés en el trabajo y mantener el equilibrio.',
                'content' => '<p>El estrés laboral es un problema común. Descubre estrategias para manejarlo efectivamente.</p><h2>Técnicas de respiración</h2><p>Practica respiración profunda cuando sientas que el estrés aumenta.</p>',
                'featured_image' => '/storage/img/blog/entrevista_doctora-scaled.webp',
                'author_name' => 'Psic. Carolina López',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now()->subDays(10),
            ],
            [
                'category' => 'salud-espiritual',
                'title' => 'Yoga: beneficios para cuerpo y mente',
                'slug' => 'yoga-beneficios-cuerpo-mente',
                'excerpt' => 'Descubre cómo el yoga puede transformar tu salud física y espiritual.',
                'content' => '<p>El yoga es una práctica antigua que conecta cuerpo, mente y espíritu. Conoce sus múltiples beneficios.</p><h2>Beneficios físicos</h2><p>Mejora la flexibilidad, fortalece músculos y mejora la postura.</p>',
                'featured_image' => '/storage/img/blog/chica-sentada.jpg',
                'author_name' => 'Instructora Yoga Lucía Fernández',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now()->subDays(4),
            ],
            [
                'category' => 'salud-familiar',
                'title' => 'Recetas saludables para toda la familia',
                'slug' => 'recetas-saludables-familia',
                'excerpt' => 'Prepara comidas saludables y deliciosas que toda la familia disfrutará.',
                'content' => '<p>Comer saludable en familia es posible. Aquí te compartimos recetas fáciles y nutritivas.</p><h2>Desayuno energético</h2><p>Avena con frutas frescas y nueces para empezar el día con energía.</p>',
                'featured_image' => '/storage/img/blog/Familia-en-picnic-scaled.webp',
                'author_name' => 'Chef Roberto Torres',
                'is_published' => true,
                'is_featured' => true,
                'published_at' => now()->subDays(1),
            ],
            [
                'category' => 'salud-familiar',
                'title' => 'Vitaminas esenciales para niños',
                'slug' => 'vitaminas-esenciales-ninos',
                'excerpt' => 'Guía sobre las vitaminas que tus hijos necesitan para un crecimiento saludable.',
                'content' => '<p>Los niños necesitan nutrientes específicos para su crecimiento. Conoce las vitaminas esenciales.</p><h2>Vitamina D</h2><p>Esencial para huesos fuertes y el sistema inmune.</p>',
                'featured_image' => '/storage/img/blog/Familia-en-picnic-scaled.webp',
                'author_name' => 'Dra. Pediatrics Carmen Reyes',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now()->subDays(6),
            ],
            [
                'category' => 'salud-alimentaria',
                'title' => 'Smoothies detox para después de Fiestas',
                'slug' => 'smoothies-detox-fiestas',
                'excerpt' => 'Recetas de smoothies desintoxicantes para recuperarte después de las celebraciones.',
                'content' => '<p>Después de las fiestas, tu cuerpo necesita un impulso detox. Estos smoothies te ayudarán a recuperarte.</p><h2>Smoothie Verde</h2><p>Espinacas, manzana verde, pepino y limón para limpiar tu organismo.</p>',
                'featured_image' => '/storage/img/blog/laptop.jpg',
                'author_name' => 'Nutricionista Ana Martínez',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now()->subDays(8),
            ],
        ];

        foreach ($posts as $postData) {
            $categorySlug = $postData['category'];
            unset($postData['category']);

            $postData['blog_category_id'] = $categories[$categorySlug] ?? null;

            $existing = BlogPost::where('slug', $postData['slug'])->first();
            if ($existing) {
                continue;
            }

            $post = BlogPost::create($postData);

            $comments = [
                ['author_name' => 'Juan Pérez', 'author_email' => 'juan@email.com', 'content' => 'Excelente artículo, muy informativo.'],
                ['author_name' => 'María López', 'author_email' => 'maria@email.com', 'content' => 'Gracias por compartir estos consejos.'],
            ];

            foreach ($comments as $comment) {
                BlogComment::create([
                    'blog_post_id' => $post->id,
                    'author_name' => $comment['author_name'],
                    'author_email' => $comment['author_email'],
                    'content' => $comment['content'],
                    'is_approved' => true,
                ]);
            }
        }

        $this->command->info('Blog posts created: '.count($posts));
    }

    private function podcasts(): void
    {
        $podcasts = [
            [
                'title' => 'Podcast BioBlog',
                'description' => 'Entrevistas y conversaciones sobre salud y bienestar',
                'image' => '/storage/img/blog/entrevista_doctora-scaled.webp',
                'audio_url' => 'https://example.com/podcasts/ep001.mp3',
                'duration' => '45:30',
                'is_published' => true,
                'published_at' => now()->subDays(1),
            ],
        ];

        foreach ($podcasts as $podcast) {
            BlogPodcast::updateOrCreate(
                ['title' => $podcast['title']],
                $podcast
            );
        }

        $this->command->info('Blog podcasts created: '.count($podcasts));
    }

    private function videos(): void
    {
        $videos = [
            [
                'title' => 'Mejorando mi calidad de vida',
                'category' => 'salud-alimentaria',
                'category_label' => 'SALUD ALIMENTARIA',
                'youtube_id' => 'ACPkTAPJLnM',
                'is_published' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Cambiar mis hábitos para bien',
                'category' => 'salud-alimentaria',
                'category_label' => 'SALUD ALIMENTARIA',
                'youtube_id' => 'E0bria5w1lc',
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Salud, Belleza y Bienestar',
                'category' => 'salud-belleza',
                'category_label' => 'SALUD Y BELLEZA',
                'youtube_id' => 'wiJzsSP_5Ao',
                'is_published' => true,
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Impacto de las emociones en la salud',
                'category' => 'salud-emocional',
                'category_label' => 'SALUD EMOCIONAL',
                'youtube_id' => '9reNgtVBcJ4',
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'La salud mental también es importante',
                'category' => 'salud-emocional',
                'category_label' => 'SALUD EMOCIONAL',
                'youtube_id' => 'G2vjOVda6og',
                'is_published' => true,
                'published_at' => now()->subDays(6),
            ],
            [
                'title' => 'COMIDA SALUDABLE',
                'category' => 'salud-alimentaria',
                'category_label' => 'SALUD ALIMENTARIA',
                'youtube_id' => 'wiJzsSP_5Ao',
                'is_published' => true,
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => 'Relaciones y Salud Social',
                'category' => 'salud-familiar',
                'category_label' => 'SALUD FAMILIAR',
                'youtube_id' => 'wiJzsSP_5Ao',
                'is_published' => true,
                'published_at' => now()->subDays(8),
            ],
            [
                'title' => 'Bienestar Emocional Diario',
                'category' => 'salud-emocional',
                'category_label' => 'SALUD EMOCIONAL',
                'youtube_id' => 'ACPkTAPJLnM',
                'is_published' => true,
                'published_at' => now()->subDays(9),
            ],
            [
                'title' => 'Nuevas Historias Inspiradoras',
                'category' => 'salud-espiritual',
                'category_label' => 'SALUD ESPIRITUAL',
                'youtube_id' => 'E0bria5w1lc',
                'is_published' => true,
                'published_at' => now()->subDays(10),
            ],
        ];

        foreach ($videos as $video) {
            BlogVideo::updateOrCreate(
                ['title' => $video['title']],
                $video
            );
        }

        $this->command->info('Blog videos created: '.count($videos));
    }
}
