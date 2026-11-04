<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistributorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rapikan kode distributor sebelum divalidasi.
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
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9._-]+$/', 'unique:distributors,code'],
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
            'code.required' => 'Kode distributor wajib diisi.',
            'code.max' => 'Kode distributor maksimal 50 karakter.',
            'code.regex' => 'Kode distributor hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.',
            'code.unique' => 'Kode distributor sudah digunakan.',
            'name.required' => 'Nama distributor wajib diisi.',
            'name.max' => 'Nama distributor maksimal 255 karakter.',
            'is_active.required' => 'Status wajib dipilih.',
            'is_active.boolean' => 'Status tidak valid.',
        ];
    }
}
