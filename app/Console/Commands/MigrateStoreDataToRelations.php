<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Models\StoreBanking;
use App\Models\StoreContact;
use App\Models\StoreLegalRepresentative;
use App\Models\StoreLocation;
use App\Models\StoreSocialMedia;
use Illuminate\Console\Command;

class MigrateStoreDataToRelations extends Command
{
    protected $signature = 'store:migrate-data-to-relations {store_id? : ID de la tienda}';

    protected $description = 'Migra datos de stores a las tablas relacionadas';

    public function handle(): int
    {
        $storeId = $this->argument('store_id');

        if ($storeId) {
            $stores = Store::where('id', $storeId)->get();
            $this->info("Migrando tienda ID: {$storeId}");
        } else {
            $stores = Store::all();
            $this->info('Migrando todas las tiendas ('.$stores->count().')');
        }

        $migrated = 0;

        foreach ($stores as $store) {
            $this->migrateContacts($store);
            $this->migrateSocialMedia($store);
            $this->migrateLegalRepresentative($store);
            $this->migrateBanking($store);
            $this->migrateLocation($store);
            $migrated++;
            $this->line("  ✓ Tienda ID {$store->id} migrada");
        }

        $this->info("Total migrado: {$migrated} tiendas");

        return Command::SUCCESS;
    }

    private function migrateContacts(Store $store): void
    {
        if ($store->phone) {
            StoreContact::updateOrCreate(
                ['store_id' => $store->id, 'contact_type' => 'phone', 'contact_role' => 'primary'],
                ['value' => $store->phone, 'is_primary' => true]
            );
        }

        if ($store->whatsapp) {
            StoreContact::updateOrCreate(
                ['store_id' => $store->id, 'contact_type' => 'whatsapp', 'contact_role' => 'primary'],
                ['value' => $store->whatsapp, 'is_primary' => true]
            );
        }
    }

    private function migrateSocialMedia(Store $store): void
    {
        $socialFields = [
            'instagram' => 'instagram',
            'facebook' => 'facebook',
            'tiktok' => 'tiktok',
            'youtube' => 'youtube',
            'twitter' => 'twitter',
            'linkedin' => 'linkedin',
            'website' => 'website',
        ];

        foreach ($socialFields as $field => $platform) {
            $value = $store->$field;
            if (! empty($value)) {
                StoreSocialMedia::updateOrCreate(
                    ['store_id' => $store->id, 'platform' => $platform],
                    [
                        'username' => $value,
                        'profile_url' => $this->buildSocialUrl($platform, $value),
                        'is_primary' => true,
                    ]
                );
            }
        }
    }

    private function migrateLegalRepresentative(Store $store): void
    {
        if ($store->legal_representative_name || $store->legal_representative_dni) {
            StoreLegalRepresentative::updateOrCreate(
                ['store_id' => $store->id, 'is_current' => true],
                [
                    'nombre' => $store->legal_representative_name,
                    'dni' => $store->legal_representative_dni,
                    'foto_url' => $store->legal_representative_photo,
                    'direccion_fiscal' => $store->fiscal_address,
                ]
            );
        }
    }

    private function migrateBanking(Store $store): void
    {
        if ($store->account_bcp) {
            StoreBanking::updateOrCreate(
                ['store_id' => $store->id, 'is_primary' => true],
                [
                    'bank_name' => 'BCP',
                    'bank_code' => 'BCP',
                    'account_type' => 'savings',
                    'account_number' => $store->account_bcp,
                    'cci' => $store->cci,
                    'currency' => 'PEN',
                    'is_verified' => false,
                ]
            );
        }
    }

    private function migrateLocation(Store $store): void
    {
        if ($store->address) {
            StoreLocation::updateOrCreate(
                ['store_id' => $store->id, 'is_primary' => true],
                [
                    'location_type' => 'store',
                    'address' => $store->address,
                    'country' => 'PE',
                ]
            );
        }
    }

    private function buildSocialUrl(string $platform, string $value): string
    {
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return match ($platform) {
            'instagram' => "https://instagram.com/{$value}",
            'facebook' => "https://facebook.com/{$value}",
            'tiktok' => "https://tiktok.com/@{$value}",
            'youtube' => "https://youtube.com/@{$value}",
            'twitter' => "https://twitter.com/{$value}",
            'linkedin' => "https://linkedin.com/in/{$value}",
            'website' => str_starts_with($value, 'www.') ? "https://{$value}" : $value,
            default => $value,
        };
    }
}
