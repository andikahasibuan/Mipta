<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecipientStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rapikan nama toko sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'market_id' => ['required', 'integer', 'exists:markets,id'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama toko wajib diisi.',
            'name.max' => 'Nama toko maksimal 255 karakter.',
            'market_id.required' => 'Pasar wajib dipilih.',
            'market_id.exists' => 'Pasar yang dipilih tidak valid.',
            'is_active.required' => 'Status wajib dipilih.',
            'is_active.boolean' => 'Status tidak valid.',
        ];
    }
}
