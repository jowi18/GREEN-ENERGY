<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VendorRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ── Step 1: Account credentials ───────────────────────────────
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],

            // ── Step 2: Owner / business info ─────────────────────────────
            'owner_first_name'             => ['required', 'string', 'max:100'],
            'owner_last_name'              => ['required', 'string', 'max:100'],
            'owner_phone'                  => ['required', 'string', 'max:30'],
            'business_name'                => ['required', 'string', 'max:255'],
            'business_type'                => ['required', 'in:sole_proprietorship,partnership,corporation,cooperative,sme,other'],
            'business_registration_number' => ['required', 'string', 'max:100', 'unique:vendors,business_registration_number'],
            'business_phone'               => ['required', 'string', 'max:30'],
            'business_email'               => ['nullable', 'email', 'max:255'],
            'business_website'             => ['nullable', 'url', 'max:255'],

            // ── Step 3: Business address + map ────────────────────────────
            'address_line1'   => ['required', 'string', 'max:255'],
            'address_line2'   => ['nullable', 'string', 'max:255'],
            'city'            => ['required', 'string', 'max:100'],
            'province_state'  => ['required', 'string', 'max:100'],
            'postal_code'     => ['required', 'string', 'max:20'],
            'country'         => ['required', 'string', 'max:100'],
            'latitude'        => ['required', 'numeric', 'between:-90,90'],
            'longitude'       => ['required', 'numeric', 'between:-180,180'],

            // ── Step 4: Document uploads ──────────────────────────────────
            'documents'                => ['required', 'array', 'min:1'],
            'documents.*.type'         => ['required', 'in:business_permit,government_id,proof_of_address,sme_certificate,dti_registration,sec_registration,bir_registration,other'],
            'documents.*.label'        => ['required', 'string', 'max:255'],
            'documents.*.file'         => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // 5MB per doc
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'                              => 'This email address is already registered.',
            'business_registration_number.unique'       => 'This registration number is already associated with another vendor.',
            'latitude.required'                         => 'Please pin your business location on the map.',
            'longitude.required'                        => 'Please pin your business location on the map.',
            'documents.required'                        => 'Please upload at least one supporting document.',
            'documents.*.file.mimes'                    => 'Documents must be PDF, JPG, or PNG files.',
            'documents.*.file.max'                      => 'Each document must not exceed 5MB.',
            'password.confirmed'                        => 'Password confirmation does not match.',
        ];
    }
}
