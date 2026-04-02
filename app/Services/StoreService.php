<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Store;
use Illuminate\Support\Facades\DB;

final class StoreService
{
    public function updateWithRelations(Store $store, array $data): Store
    {
        return DB::transaction(function () use ($store, $data) {
            if (array_key_exists('store', $data)) {
                if ($data['store'] !== null) {
                    $this->updateStore($store, $data['store']);
                }
            }

            if (array_key_exists('legalRepresentative', $data)) {
                if ($data['legalRepresentative'] !== null) {
                    $this->updateLegalRepresentative($store, $data['legalRepresentative']);
                }
            }

            if (array_key_exists('contacts', $data)) {
                if ($data['contacts'] !== null) {
                    $this->syncContacts($store, $data['contacts']);
                }
            }

            if (array_key_exists('banking', $data)) {
                if ($data['banking'] !== null) {
                    $this->syncBanking($store, $data['banking']);
                }
            }

            if (array_key_exists('socialMedia', $data)) {
                if ($data['socialMedia'] !== null) {
                    $this->syncSocialMedia($store, $data['socialMedia']);
                }
            }

            if (array_key_exists('location', $data)) {
                if ($data['location'] !== null) {
                    $this->updateLocation($store, $data['location']);
                }
            }

            if (array_key_exists('presentation', $data)) {
                if ($data['presentation'] !== null) {
                    $this->updatePresentation($store, $data['presentation']);
                }
            }

            return $store->fresh([
                'legalRepresentative',
                'contacts',
                'banking',
                'socialMedia',
                'locations',
                'presentation',
            ]);
        });
    }

    private function updateStore(Store $store, array $data): void
    {
        $allowedFields = [
            'ruc', 'trade_name', 'razon_social', 'nombre_comercial',
            'corporate_email', 'slug', 'description', 'activity',
            'experience_years', 'tax_condition', 'direccion_fiscal',
            'category_id', 'status', 'seller_type',
        ];

        $updateData = array_intersect_key($data, array_flip($allowedFields));

        $updateData = array_filter($updateData, fn ($value) => $value !== null);

        if (! empty($updateData)) {
            $store->update($updateData);
        }
    }

    private function updateLegalRepresentative(Store $store, array $data): void
    {
        if (empty($data['dni']) && empty($data['nombre'])) {
            return;
        }

        $store->legalRepresentatives()->where('is_current', true)->update(['is_current' => false]);

        $existing = $store->legalRepresentatives()
            ->where('dni', $data['dni'])
            ->first();

        if ($existing) {
            $existing->update([
                'nombre' => $data['nombre'] ?? $existing->nombre,
                'foto_url' => $data['foto_url'] ?? $existing->foto_url,
                'direccion_fiscal' => $data['direccion_fiscal'] ?? $existing->direccion_fiscal,
                'is_current' => true,
                'valid_from' => now()->toDateString(),
            ]);
        } else {
            $store->legalRepresentatives()->create([
                'nombre' => $data['nombre'],
                'dni' => $data['dni'],
                'foto_url' => $data['foto_url'] ?? null,
                'direccion_fiscal' => $data['direccion_fiscal'] ?? null,
                'is_current' => true,
                'valid_from' => now()->toDateString(),
            ]);
        }
    }

    private function syncContacts(Store $store, array $contacts): void
    {
        if (empty($contacts)) {
            return;
        }

        $existingContacts = $store->contacts()->get()->keyBy(fn ($c) => $c->contact_type.'-'.$c->contact_role);

        $newContactIds = [];

        foreach ($contacts as $contact) {
            $key = $contact['contact_type'].'-'.$contact['contact_role'];
            $existing = $existingContacts->get($key);

            if ($existing) {
                $existing->update([
                    'value' => $contact['value'] ?? $existing->value,
                    'label' => $contact['label'] ?? $existing->label,
                    'owner_name' => $contact['owner_name'] ?? $existing->owner_name,
                    'owner_dni' => $contact['owner_dni'] ?? $existing->owner_dni,
                    'is_verified' => $contact['is_verified'] ?? $existing->is_verified,
                    'is_primary' => $contact['is_primary'] ?? $existing->is_primary,
                ]);
                $newContactIds[] = $existing->id;
            } else {
                $newContact = $store->contacts()->create([
                    'contact_type' => $contact['contact_type'],
                    'contact_role' => $contact['contact_role'],
                    'value' => $contact['value'],
                    'label' => $contact['label'] ?? null,
                    'owner_name' => $contact['owner_name'] ?? null,
                    'owner_dni' => $contact['owner_dni'] ?? null,
                    'is_verified' => $contact['is_verified'] ?? false,
                    'is_primary' => $contact['is_primary'] ?? false,
                ]);
                $newContactIds[] = $newContact->id;
            }
        }

        $store->contacts()->whereNotIn('id', $newContactIds)->delete();
    }

    private function syncBanking(Store $store, array $banking): void
    {
        if (empty($banking)) {
            return;
        }

        $existingBanking = $store->banking()->get()->keyBy(fn ($b) => $b->bank_code);

        $newBankingIds = [];

        foreach ($banking as $bank) {
            $existing = $existingBanking->get($bank['bank_code']);

            if ($existing) {
                $existing->update([
                    'account_type' => $bank['account_type'] ?? $existing->account_type,
                    'account_number' => $bank['account_number'] ?? $existing->account_number,
                    'cci' => $bank['cci'] ?? $existing->cci,
                    'currency' => $bank['currency'] ?? $existing->currency,
                    'holder_name' => $bank['holder_name'] ?? $existing->holder_name,
                    'is_primary' => $bank['is_primary'] ?? $existing->is_primary,
                    'is_verified' => $bank['is_verified'] ?? $existing->is_verified,
                    'purpose' => $bank['purpose'] ?? $existing->purpose,
                ]);
                $newBankingIds[] = $existing->id;
            } else {
                $newBank = $store->banking()->create([
                    'bank_code' => $bank['bank_code'],
                    'bank_name' => $bank['bank_name'],
                    'account_type' => $bank['account_type'] ?? 'savings',
                    'account_number' => $bank['account_number'] ?? null,
                    'cci' => $bank['cci'] ?? null,
                    'currency' => $bank['currency'] ?? 'PEN',
                    'holder_name' => $bank['holder_name'] ?? null,
                    'is_primary' => $bank['is_primary'] ?? false,
                    'is_verified' => $bank['is_verified'] ?? false,
                    'purpose' => $bank['purpose'] ?? 'sales',
                ]);
                $newBankingIds[] = $newBank->id;
            }
        }

        $store->banking()->whereNotIn('id', $newBankingIds)->delete();
    }

    private function syncSocialMedia(Store $store, array $socialMedia): void
    {
        if (empty($socialMedia)) {
            return;
        }

        $existingSocial = $store->socialMedia()->get()->keyBy(fn ($s) => $s->platform);

        $newSocialIds = [];

        foreach ($socialMedia as $social) {
            if (empty($social['profile_url']) && empty($social['username'])) {
                continue;
            }

            $existing = $existingSocial->get($social['platform']);

            if ($existing) {
                $existing->update([
                    'username' => $social['username'] ?? $existing->username,
                    'profile_url' => $social['profile_url'] ?? $existing->profile_url,
                    'is_primary' => $social['is_primary'] ?? $existing->is_primary,
                    'is_verified' => $social['is_verified'] ?? $existing->is_verified,
                ]);
                $newSocialIds[] = $existing->id;
            } else {
                $newSocial = $store->socialMedia()->create([
                    'platform' => $social['platform'],
                    'username' => $social['username'] ?? null,
                    'profile_url' => $social['profile_url'],
                    'is_primary' => $social['is_primary'] ?? false,
                    'is_verified' => $social['is_verified'] ?? false,
                ]);
                $newSocialIds[] = $newSocial->id;
            }
        }

        $store->socialMedia()->whereNotIn('id', $newSocialIds)->delete();
    }

    private function updateLocation(Store $store, array $data): void
    {
        if (empty($data['address'])) {
            return;
        }

        $store->locations()->where('is_primary', true)->update(['is_primary' => false]);

        $existing = $store->locations()
            ->where('is_primary', false)
            ->first();

        if ($existing) {
            $existing->update([
                'location_type' => $data['location_type'] ?? $existing->location_type,
                'address' => $data['address'] ?? $existing->address,
                'district' => $data['district'] ?? $existing->district,
                'city' => $data['city'] ?? $existing->city,
                'region' => $data['region'] ?? $existing->region,
                'postal_code' => $data['postal_code'] ?? $existing->postal_code,
                'country' => $data['country'] ?? $existing->country,
                'latitude' => $data['latitude'] ?? $existing->latitude,
                'longitude' => $data['longitude'] ?? $existing->longitude,
                'is_primary' => true,
                'is_pickup_available' => $data['is_pickup_available'] ?? $existing->is_pickup_available,
                'operating_hours' => $data['operating_hours'] ?? $existing->operating_hours,
                'contact_phone' => $data['contact_phone'] ?? $existing->contact_phone,
            ]);
        } else {
            $store->locations()->create([
                'location_type' => $data['location_type'] ?? 'office',
                'address' => $data['address'],
                'district' => $data['district'] ?? null,
                'city' => $data['city'] ?? null,
                'region' => $data['region'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => $data['country'] ?? 'PE',
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'is_primary' => true,
                'is_pickup_available' => $data['is_pickup_available'] ?? false,
                'operating_hours' => $data['operating_hours'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
            ]);
        }
    }

    private function updatePresentation(Store $store, array $data): void
    {
        $existing = $store->presentation;

        if ($existing) {
            $updateData = array_filter([
                'logo' => $data['logo'] ?? null,
                'banner' => $data['banner'] ?? null,
                'banner2' => $data['banner2'] ?? null,
                'description' => $data['description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'layout' => $data['layout'] ?? null,
                'theme_color' => $data['theme_color'] ?? null,
                'custom_css' => $data['custom_css'] ?? null,
                'seo_title' => $data['seo_title'] ?? null,
                'seo_description' => $data['seo_description'] ?? null,
                'seo_keywords' => $data['seo_keywords'] ?? null,
            ], fn ($v) => $v !== null);

            if (! empty($updateData)) {
                $existing->update($updateData);
            }
        } else {
            $store->presentation()->create([
                'logo' => $data['logo'] ?? null,
                'banner' => $data['banner'] ?? null,
                'banner2' => $data['banner2'] ?? null,
                'description' => $data['description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'layout' => $data['layout'] ?? 'grid',
                'theme_color' => $data['theme_color'] ?? null,
                'custom_css' => $data['custom_css'] ?? null,
                'seo_title' => $data['seo_title'] ?? null,
                'seo_description' => $data['seo_description'] ?? null,
                'seo_keywords' => $data['seo_keywords'] ?? null,
            ]);
        }
    }
}
