<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class StoreRegisterMedia extends Command
{
    protected $signature = 'store:register-media {store_id? : ID de la tienda}';

    protected $description = 'Registra medios existentes del storage en la tabla media';

    public function handle(): int
    {
        $storeId = $this->argument('store_id') ?? 1;

        $this->info("Registrando medios para store ID: {$storeId}");

        $basePath = storage_path('app/public/usar-esto');

        $registered = 0;

        // Logo
        $logoPath = "{$basePath}/LOGO.png";
        if (file_exists($logoPath)) {
            $this->registerMedia($storeId, 'logo', $logoPath);
            $this->info('  ✓ Registrado: logo');
            $registered++;
        } else {
            $this->warn('  ✗ No encontrado: logo');
        }

        // Banner
        $bannerPath = "{$basePath}/banner_peq1.png";
        if (file_exists($bannerPath)) {
            $this->registerMedia($storeId, 'banner', $bannerPath);
            $this->info('  ✓ Registrado: banner');
            $registered++;
        } else {
            $this->warn('  ✗ No encontrado: banner');
        }

        // Banner2
        $banner2Path = "{$basePath}/banner_peq2.png";
        if (file_exists($banner2Path)) {
            $this->registerMedia($storeId, 'banner2', $banner2Path);
            $this->info('  ✓ Registrado: banner2');
            $registered++;
        } else {
            $this->warn('  ✗ No encontrado: banner2');
        }

        // Gallery
        $galleryPath = "{$basePath}/galeria-fotos";
        if (is_dir($galleryPath)) {
            $files = glob("{$galleryPath}/*.{jpg,jpeg,png,webp}", GLOB_BRACE);
            foreach ($files as $file) {
                $this->registerMedia($storeId, 'gallery', $file);
                $this->info('  ✓ Registrado: gallery - '.basename($file));
                $registered++;
            }
        }

        $this->info("Total registrado: {$registered}");

        // Verify
        $store = \App\Models\Store::with('media')->find($storeId);
        $this->info('Medios en BD: '.$store->media->pluck('collection_name', 'file_name'));

        return Command::SUCCESS;
    }

    private function findFileForCollection(string $basePath, int $storeId, array $patterns, array $extensions): ?string
    {
        $dirs = [
            "{$basePath}/{$storeId}",
            "{$basePath}/img/stores",
            "{$basePath}/img/store",
        ];

        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            $files = File::allFiles($dir);
            foreach ($files as $file) {
                $filename = strtolower($file->getFilename());
                foreach ($patterns as $pattern) {
                    if (fnmatch($pattern, $filename)) {
                        return $file->getPathname();
                    }
                }
            }
        }

        return null;
    }

    private function registerMedia(int $storeId, string $collection, string $filePath): void
    {
        $file = new \SplFileInfo($filePath);
        $filename = $file->getFilename();

        Media::create([
            'model_type' => 'App\Models\Store',
            'model_id' => $storeId,
            'collection_name' => $collection,
            'name' => pathinfo($filename, PATHINFO_FILENAME),
            'file_name' => $filename,
            'mime_type' => mime_content_type($filePath),
            'disk' => 'public',
            'size' => $file->getSize(),
            'manipulations' => json_encode([]),
            'custom_properties' => json_encode([]),
            'generated_conversions' => json_encode([]),
            'responsive_images' => json_encode([]),
        ]);
    }
}
