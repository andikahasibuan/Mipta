<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rapikan kode dan nama pasar sebelum divalidasi.
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
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9._-]+$/', 'unique:markets,code'],
            'name' => ['required', 'string', 'max:255'],
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
            'code.required' => 'Kode pasar wajib diisi.',
            'code.max' => 'Kode pasar maksimal 50 karakter.',
            'code.regex' => 'Kode pasar hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.',
            'code.unique' => 'Kode pasar sudah digunakan.',
            'name.required' => 'Nama pasar wajib diisi.',
            'name.max' => 'Nama pasar maksimal 255 karakter.',
            'is_active.required' => 'Status wajib dipilih.',
            'is_active.boolean' => 'Status tidak valid.',
        ];
    }
}
