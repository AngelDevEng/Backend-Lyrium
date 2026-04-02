<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Store extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'ruc',
        'trade_name',
        'razon_social',
        'commercial_name',
        'corporate_email',
        'slug',
        'description',
        'activity',
        'logo',
        'banner',
        'banner2',
        'store_name',
        'category_id',
        'address',
        'phone',
        'status',
        'seller_type',
        'strikes',
        'commission_rate',
        'legal_representative_name',
        'legal_representative_dni',
        'legal_representative_photo',
        'experience_years',
        'tax_condition',
        'fiscal_address',
        'account_bcp',
        'cci',
        'bank_secondary',
        'instagram',
        'facebook',
        'tiktok',
        'whatsapp',
        'youtube',
        'twitter',
        'linkedin',
        'website',
        'policies',
        'gallery',
        'layout',
        'profile_status',
        'profile_updated_at',
        'approved_at',
        'banned_at',
    ];

    protected function casts(): array
    {
        return [
            'strikes' => 'integer',
            'commission_rate' => 'decimal:4',
            'experience_years' => 'integer',
            'approved_at' => 'datetime',
            'banned_at' => 'datetime',
            'bank_secondary' => 'array',
            'gallery' => 'array',
            'profile_status' => 'string',
            'profile_updated_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function legalRepresentative(): HasOne
    {
        return $this->hasOne(StoreLegalRepresentative::class)
            ->where('is_current', true);
    }

    public function legalRepresentatives(): HasMany
    {
        return $this->hasMany(StoreLegalRepresentative::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(StoreContact::class);
    }

    public function primaryContact(): HasOne
    {
        return $this->hasOne(StoreContact::class)
            ->where('is_primary', true);
    }

    public function banking(): HasMany
    {
        return $this->hasMany(StoreBanking::class);
    }

    public function primaryBanking(): HasOne
    {
        return $this->hasOne(StoreBanking::class)
            ->where('is_primary', true);
    }

    public function socialMedia(): HasMany
    {
        return $this->hasMany(StoreSocialMedia::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(StoreLocation::class);
    }

    public function primaryLocation(): HasOne
    {
        return $this->hasOne(StoreLocation::class)
            ->where('is_primary', true);
    }

    public function presentation(): HasOne
    {
        return $this->hasOne(StorePresentation::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(StoreBranch::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_store')->withTimestamps();
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }

    public function badges(): HasMany
    {
        return $this->hasMany(StoreBadge::class);
    }

    public function activeBadges(): HasMany
    {
        return $this->hasMany(StoreBadge::class)->where('is_active', true);
    }

    public function insignia(): HasOne
    {
        return $this->hasOne(StoreInsignia::class);
    }

    public function hasPremiumInsignia(): bool
    {
        return $this->insignia?->has_premium === true;
    }

    public function canRequestInsignia(): bool
    {
        $insignia = $this->insignia;

        if ($insignia?->has_premium) {
            return false;
        }

        return $insignia?->status !== 'pending';
    }

    public function calculateAndSyncBadges(): void
    {
        $badges = Badge::all()->keyBy('type');

        $verified = $badges->get('verified');
        if ($verified && $this->approved_at !== null) {
            $this->grantBadge($verified, 'verified');
        }

        $topSeller = $badges->get('top_seller');
        if ($topSeller && $this->rating >= 4.5 && $this->total_sales > 50) {
            $this->grantBadge($topSeller, 'top_seller');
        }

        $expressShipping = $badges->get('express_shipping');
        if ($expressShipping) {
            $expressDeliveries = $this->countExpressDeliveries();
            if ($expressDeliveries >= 20) {
                $this->grantBadge($expressShipping, 'express_shipping');
            }
        }
    }

    private function grantBadge(Badge $badge, string $type): void
    {
        $existing = $this->badges()->where('badge_id', $badge->id)->first();

        if (! $existing) {
            $this->badges()->create([
                'badge_id' => $badge->id,
                'is_active' => true,
                'earned_at' => now(),
            ]);
        } elseif (! $existing->is_active) {
            $existing->update([
                'is_active' => true,
                'earned_at' => $existing->earned_at ?? now(),
            ]);
        }
    }

    private function countExpressDeliveries(): int
    {
        return \App\Models\Shipment::whereHas('order.items', function ($query) {
            $query->where('store_id', $this->id);
        })
            ->where('status', 'delivered')
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, delivered_at) <= 12')
            ->count();
    }

    public function requestInsignia(): bool
    {
        if (! $this->canRequestInsignia()) {
            return false;
        }

        StoreInsignia::updateOrCreate(
            ['store_id' => $this->id],
            ['status' => 'pending']
        );

        return true;
    }

    public function grantPremiumInsignia(int $adminId): void
    {
        StoreInsignia::updateOrCreate(
            ['store_id' => $this->id],
            [
                'has_premium' => true,
                'status' => 'approved',
                'granted_at' => now(),
                'granted_by' => $adminId,
            ]
        );
    }

    public function revokePremiumInsignia(): void
    {
        $this->insignia?->update([
            'has_premium' => false,
            'status' => 'rejected',
        ]);
    }

    public function rejectInsigniaRequest(): void
    {
        $this->insignia?->update(['status' => 'rejected']);
    }

    public function addStrike(): void
    {
        $this->increment('strikes');
        if ($this->strikes >= 3) {
            $this->update([
                'status' => 'banned',
                'banned_at' => now(),
            ]);
        }
    }

    public function getPolicyUrl(string $type): ?string
    {
        $media = $this->media()
            ->where('collection_name', 'policies')
            ->whereJsonContains('custom_properties->type', $type)
            ->first();

        return $media?->getUrl();
    }

    public function getMediaUrl(string $collection): ?string
    {
        return $this->getFirstMediaUrl($collection);
    }

    public function getGalleryUrls(): array
    {
        return $this->getMedia('gallery')
            ->map(fn ($media) => $media->getUrl())
            ->toArray();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->useDisk('public');

        $this->addMediaCollection('banner')
            ->useDisk('public');

        $this->addMediaCollection('banner2')
            ->useDisk('public');

        $this->addMediaCollection('gallery')
            ->useDisk('public');

        $this->addMediaCollection('policies')
            ->useDisk('public');
    }

    public function getStoreNameAttribute(): ?string
    {
        return $this->attributes['store_name']
            ?? $this->attributes['trade_name']
            ?? $this->attributes['store_name_deprecated']
            ?? $this->attributes['trade_name_deprecated']
            ?? null;
    }

    public function getRepLegalNombreAttribute(): ?string
    {
        return $this->attributes['legal_representative_name']
            ?? $this->attributes['rep_legal_nombre_deprecated']
            ?? null;
    }

    public function getRepLegalDniAttribute(): ?string
    {
        return $this->attributes['legal_representative_dni']
            ?? $this->attributes['rep_legal_dni_deprecated']
            ?? null;
    }

    public function getRepLegalFotoAttribute(): ?string
    {
        return $this->attributes['legal_representative_photo']
            ?? $this->attributes['rep_legal_foto_deprecated']
            ?? null;
    }

    public function getDireccionFiscalAttribute(): ?string
    {
        return $this->attributes['fiscal_address']
            ?? $this->attributes['direccion_fiscal_deprecated']
            ?? null;
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->attributes['phone']
            ?? $this->attributes['phone_deprecated']
            ?? null;
    }

    public function getWhatsappAttribute(): ?string
    {
        return $this->attributes['whatsapp']
            ?? $this->attributes['whatsapp_deprecated']
            ?? null;
    }

    public function getCuentaBcpAttribute(): ?string
    {
        return $this->attributes['account_bcp']
            ?? $this->attributes['cuenta_bcp_deprecated']
            ?? null;
    }

    public function getCciAttribute(): ?string
    {
        return $this->attributes['cci']
            ?? $this->attributes['cci_deprecated']
            ?? null;
    }

    public function getBankSecondaryAttribute(): ?array
    {
        return $this->attributes['bank_secondary']
            ?? $this->attributes['bank_secondary_deprecated']
            ?? null;
    }

    public function getInstagramAttribute(): ?string
    {
        return $this->attributes['instagram']
            ?? $this->attributes['instagram_deprecated']
            ?? null;
    }

    public function getFacebookAttribute(): ?string
    {
        return $this->attributes['facebook']
            ?? $this->attributes['facebook_deprecated']
            ?? null;
    }

    public function getTiktokAttribute(): ?string
    {
        return $this->attributes['tiktok']
            ?? $this->attributes['tiktok_deprecated']
            ?? null;
    }

    public function getYoutubeAttribute(): ?string
    {
        return $this->attributes['youtube']
            ?? $this->attributes['youtube_deprecated']
            ?? null;
    }

    public function getTwitterAttribute(): ?string
    {
        return $this->attributes['twitter']
            ?? $this->attributes['twitter_deprecated']
            ?? null;
    }

    public function getLinkedinAttribute(): ?string
    {
        return $this->attributes['linkedin']
            ?? $this->attributes['linkedin_deprecated']
            ?? null;
    }

    public function getWebsiteAttribute(): ?string
    {
        return $this->attributes['website']
            ?? $this->attributes['website_deprecated']
            ?? null;
    }

    public function getAddressAttribute(): ?string
    {
        return $this->attributes['address']
            ?? $this->attributes['address_deprecated']
            ?? null;
    }

    public function getTradeNameAttribute(): ?string
    {
        return $this->attributes['trade_name']
            ?? $this->attributes['trade_name_deprecated']
            ?? null;
    }

    public function getRazonSocialAttribute(): ?string
    {
        return $this->attributes['razon_social'] ?? null;
    }

    public function getNombreComercialAttribute(): ?string
    {
        return $this->attributes['commercial_name']
            ?? $this->attributes['nombre_comercial_deprecated']
            ?? $this->attributes['trade_name']
            ?? $this->attributes['trade_name_deprecated']
            ?? null;
    }

    public function getLogoAttribute(): ?string
    {
        return $this->attributes['logo']
            ?? $this->attributes['logo_deprecated']
            ?? null;
    }

    public function getBannerAttribute(): ?string
    {
        return $this->attributes['banner']
            ?? $this->attributes['banner_deprecated']
            ?? null;
    }

    public function getBanner2Attribute(): ?string
    {
        return $this->attributes['banner2']
            ?? $this->attributes['banner2_deprecated']
            ?? null;
    }

    public function getPoliciesAttribute(): ?string
    {
        return $this->attributes['policies']
            ?? $this->attributes['policies_deprecated']
            ?? null;
    }

    public function getGalleryAttribute(): ?array
    {
        $value = $this->attributes['gallery'] ?? null;

        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    public function getTotalOrdersAttribute(): int
    {
        return \App\Models\OrderItem::where('store_id', $this->id)
            ->distinct('order_id')
            ->count('order_id');
    }
}
