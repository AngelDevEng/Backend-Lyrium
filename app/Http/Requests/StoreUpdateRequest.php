<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->legalRepresentativeRules(),
            $this->contactsRules(),
            $this->bankingRules(),
            $this->socialMediaRules(),
            $this->locationRules(),
            $this->presentationRules()
        );
    }

    private function baseRules(): array
    {
        return [
            'store' => ['nullable', 'array'],
            'store.trade_name' => ['sometimes', 'string', 'max:255'],
            'store.razon_social' => ['nullable', 'string', 'max:255'],
            'store.nombre_comercial' => ['nullable', 'string', 'max:255'],
            'store.corporate_email' => ['sometimes', 'email', 'max:255'],
            'store.description' => ['nullable', 'string'],
            'store.activity' => ['nullable', 'string', 'max:255'],
            'store.logo' => ['nullable', 'string', 'max:500'],
            'store.banner' => ['nullable', 'string', 'max:500'],
            'store.store_name' => ['nullable', 'string', 'max:255'],
            'store.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'store.address' => ['nullable', 'string'],
            'store.phone' => ['nullable', 'string', 'max:20'],
            'store.seller_type' => ['sometimes', 'string', 'in:products,services,both'],
            'store.experience_years' => ['nullable', 'integer', 'min:0', 'max:100'],
            'store.tax_condition' => ['nullable', 'string', 'max:100'],
            'store.direccion_fiscal' => ['nullable', 'string'],
            'store.policies' => ['nullable', 'string'],
            'store.gallery' => ['nullable', 'array'],
            'store.gallery.*' => ['string', 'max:500'],
            'store.layout' => ['nullable', 'string', 'in:1,2,3'],

            // Legacy fields (backward compatibility)
            'trade_name' => ['sometimes', 'string', 'max:255'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'corporate_email' => ['sometimes', 'email', 'max:255'],
            'description' => ['nullable', 'string'],
            'activity' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:500'],
            'banner' => ['nullable', 'string', 'max:500'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'seller_type' => ['sometimes', 'string', 'in:products,services,both'],
            'rep_legal_nombre' => ['nullable', 'string', 'max:255'],
            'rep_legal_dni' => ['nullable', 'string', 'max:20'],
            'rep_legal_foto' => ['nullable', 'string', 'max:500'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:100'],
            'tax_condition' => ['nullable', 'string', 'max:100'],
            'direccion_fiscal' => ['nullable', 'string'],
            'cuenta_bcp' => ['nullable', 'string', 'max:50'],
            'cci' => ['nullable', 'string', 'max:50'],
            'bank_secondary' => ['nullable', 'array'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'tiktok' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'policies' => ['nullable', 'string'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['string', 'max:500'],
        ];
    }

    private function legalRepresentativeRules(): array
    {
        return [
            'legalRepresentative' => ['nullable', 'array'],
            'legalRepresentative.id' => ['nullable', 'integer', 'exists:store_legal_representatives,id'],
            'legalRepresentative.nombre' => ['required_with:legalRepresentative', 'string', 'max:255'],
            'legalRepresentative.dni' => ['required_with:legalRepresentative', 'string', 'max:8'],
            'legalRepresentative.foto_url' => ['nullable', 'string', 'max:500'],
            'legalRepresentative.direccion_fiscal' => ['nullable', 'string'],
        ];
    }

    private function contactsRules(): array
    {
        return [
            'contacts' => ['nullable', 'array'],
            'contacts.*.contact_type' => ['required', 'string', 'in:phone,email,whatsapp,landline'],
            'contacts.*.contact_role' => ['required', 'string', 'in:primary,secondary,admin,support,finance'],
            'contacts.*.value' => ['required', 'string', 'max:100'],
            'contacts.*.label' => ['nullable', 'string', 'max:100'],
            'contacts.*.owner_name' => ['nullable', 'string', 'max:255'],
            'contacts.*.owner_dni' => ['nullable', 'string', 'max:8'],
            'contacts.*.is_verified' => ['nullable', 'boolean'],
            'contacts.*.is_primary' => ['nullable', 'boolean'],
        ];
    }

    private function bankingRules(): array
    {
        return [
            'banking' => ['nullable', 'array'],
            'banking.*.bank_code' => ['required', 'string', 'max:10'],
            'banking.*.bank_name' => ['required', 'string', 'max:50'],
            'banking.*.account_type' => ['nullable', 'string', 'in:savings,checking,cci'],
            'banking.*.account_number' => ['nullable', 'string', 'max:30'],
            'banking.*.cci' => ['nullable', 'string', 'max:20'],
            'banking.*.currency' => ['nullable', 'string', 'in:PEN,USD,EUR'],
            'banking.*.holder_name' => ['nullable', 'string', 'max:255'],
            'banking.*.is_primary' => ['nullable', 'boolean'],
            'banking.*.is_verified' => ['nullable', 'boolean'],
            'banking.*.purpose' => ['nullable', 'string', 'in:sales,purchases,payroll,taxes'],
        ];
    }

    private function socialMediaRules(): array
    {
        return [
            'socialMedia' => ['nullable', 'array'],
            'socialMedia.*.platform' => ['required', 'string', 'in:instagram,facebook,tiktok,twitter,linkedin,youtube,pinterest'],
            'socialMedia.*.username' => ['nullable', 'string', 'max:100'],
            'socialMedia.*.profile_url' => ['nullable', 'string', 'max:500'],
            'socialMedia.*.followers_count' => ['nullable', 'integer'],
            'socialMedia.*.is_verified' => ['nullable', 'boolean'],
            'socialMedia.*.is_primary' => ['nullable', 'boolean'],
        ];
    }

    private function locationRules(): array
    {
        return [
            'location' => ['nullable', 'array'],
            'location.id' => ['nullable', 'integer', 'exists:store_locations,id'],
            'location.location_type' => ['nullable', 'string', 'in:warehouse,store,office,pickup_point'],
            'location.address' => ['required_with:location', 'string'],
            'location.district' => ['nullable', 'string', 'max:100'],
            'location.city' => ['nullable', 'string', 'max:100'],
            'location.region' => ['nullable', 'string', 'max:100'],
            'location.postal_code' => ['nullable', 'string', 'max:10'],
            'location.country' => ['nullable', 'string', 'size:2'],
            'location.latitude' => ['nullable', 'numeric'],
            'location.longitude' => ['nullable', 'numeric'],
            'location.is_primary' => ['nullable', 'boolean'],
            'location.is_pickup_available' => ['nullable', 'boolean'],
            'location.operating_hours' => ['nullable', 'array'],
            'location.contact_phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    private function presentationRules(): array
    {
        return [
            'presentation' => ['nullable', 'array'],
            'presentation.logo' => ['nullable', 'string', 'max:500'],
            'presentation.banner' => ['nullable', 'string', 'max:500'],
            'presentation.banner2' => ['nullable', 'string', 'max:500'],
            'presentation.description' => ['nullable', 'string'],
            'presentation.short_description' => ['nullable', 'string', 'max:500'],
            'presentation.layout' => ['nullable', 'string', 'in:grid,list,masonry'],
            'presentation.theme_color' => ['nullable', 'string', 'max:7'],
            'presentation.custom_css' => ['nullable', 'string'],
            'presentation.seo_title' => ['nullable', 'string', 'max:255'],
            'presentation.seo_description' => ['nullable', 'string', 'max:500'],
            'presentation.seo_keywords' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'seller_type.in' => 'El tipo de vendedor debe ser: products, services o both.',
            'location.address.required_with' => 'La dirección es requerida cuando se proporciona ubicación.',
        ];
    }
}
