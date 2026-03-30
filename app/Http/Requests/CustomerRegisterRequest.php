<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'email'              => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'              => ['required', 'string', 'max:30'],
            'password'           => ['required', 'min:8', 'confirmed'],
            'address_line1'      => ['nullable', 'string', 'max:255'],
            'city'               => ['nullable', 'string', 'max:100'],
            'province_state'     => ['nullable', 'string', 'max:100'],
            'postal_code'        => ['nullable', 'string', 'max:20'],
            'government_id_type' => ['required', 'string', 'max:100'],
            'government_id'      => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'terms'              => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'             => 'This email address is already registered.',
            'government_id.required'   => 'Please upload a government-issued ID.',
            'government_id.mimes'      => 'ID must be a PDF, JPG, or PNG file.',
            'terms.accepted'           => 'You must accept the terms and conditions.',
        ];
    }
}
