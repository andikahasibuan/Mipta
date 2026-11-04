<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecipientStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rapikan kode dan nama toko sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
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
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9._-]+$/', 'unique:recipient_stores,code'],
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
            'code.required' => 'Kode toko wajib diisi.',
            'code.max' => 'Kode toko maksimal 50 karakter.',
            'code.regex' => 'Kode toko hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.',
            'code.unique' => 'Kode toko sudah digunakan.',
            'name.required' => 'Nama toko wajib diisi.',
            'name.max' => 'Nama toko maksimal 255 karakter.',
            'market_id.required' => 'Pasar wajib dipilih.',
            'market_id.exists' => 'Pasar yang dipilih tidak valid.',
            'is_active.required' => 'Status wajib dipilih.',
            'is_active.boolean' => 'Status tidak valid.',
        ];
    }
}
