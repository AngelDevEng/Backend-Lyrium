<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'userId' => $this->owner_id,
            'storeName' => $this->store_name,
            'slug' => $this->slug,
            'logo' => $this->getMediaUrl('logo'),
            'banner' => $this->getMediaUrl('banner'),
            'banner2' => $this->getMediaUrl('banner2'),
            'gallery' => $this->getGalleryUrls(),
            'description' => $this->description,
            'activity' => $this->activity,
            'email' => $this->corporate_email,
            'ruc' => $this->ruc,
            'razon_social' => $this->razon_social,
            'nombre_comercial' => $this->nombre_comercial,
            'experience_years' => $this->experience_years,
            'tax_condition' => $this->tax_condition,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'category_id' => $this->category_id,
            'status' => $this->status,
            'sellerType' => $this->seller_type,
            'strikes' => $this->strikes,
            'commissionRate' => (float) $this->commission_rate,
            'totalSales' => (int) $this->total_sales,
            'totalOrders' => (int) $this->total_orders,
            'rating' => (float) $this->rating,
            'registeredAt' => $this->created_at?->toIso8601String(),
            'verifiedAt' => $this->approved_at?->toIso8601String(),
            'legalRepresentative' => $this->whenLoaded('legalRepresentative', fn () => $this->legalRepresentative ? [
                'nombre' => $this->legalRepresentative->nombre,
                'dni' => $this->legalRepresentative->dni,
                'foto_url' => $this->legalRepresentative->foto_url,
            ] : null),
            'contacts' => $this->whenLoaded('contacts', fn () => StoreContactResource::collection($this->contacts)),
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'banking' => $this->whenLoaded('banking', fn () => StoreBankingResource::collection($this->banking)),
            'cuenta_bcp' => $this->cuenta_bcp,
            'cci' => $this->cci,
            'socialMedia' => $this->whenLoaded('socialMedia', fn () => StoreSocialMediaResource::collection($this->socialMedia)),
            'social' => [
                'instagram' => $this->instagram,
                'facebook' => $this->facebook,
                'tiktok' => $this->tiktok,
                'whatsapp' => $this->whatsapp,
                'youtube' => $this->youtube,
                'twitter' => $this->twitter,
                'linkedin' => $this->linkedin,
                'website' => $this->website,
            ],
            'locations' => $this->whenLoaded('locations', fn () => StoreLocationResource::collection($this->locations)),
            'address' => $this->address,
            'direccion_fiscal' => $this->direccion_fiscal,
            'presentation' => $this->whenLoaded('presentation', fn () => $this->presentation ? [
                'layout' => $this->presentation->layout,
                'theme_color' => $this->presentation->theme_color,
                'seo_title' => $this->presentation->seo_title,
                'seo_description' => $this->presentation->seo_description,
            ] : null),
            'policies' => $this->policies,
            'policyFiles' => [
                'shipping' => $this->getPolicyUrl('shipping'),
                'return' => $this->getPolicyUrl('return'),
                'privacy' => $this->getPolicyUrl('privacy'),
            ],
            'insignia' => $this->whenLoaded('insignia', fn () => $this->insignia ? [
                'has_premium' => $this->insignia->has_premium,
                'status' => $this->insignia->status,
                'granted_at' => $this->insignia->granted_at?->toIso8601String(),
            ] : null),
            'canRequestInsignia' => $this->canRequestInsignia(),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'subscription' => $this->whenLoaded('subscription'),
            'branches' => $this->whenLoaded('branches', fn () => $this->branches->map(fn ($branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
                'address' => $branch->address,
                'city' => $branch->city,
                'phone' => $branch->phone,
                'hours' => $branch->hours,
                'is_principal' => $branch->is_principal,
                'maps_url' => $branch->maps_url,
                'is_active' => $branch->is_active,
            ])),
        ];
    }
}
