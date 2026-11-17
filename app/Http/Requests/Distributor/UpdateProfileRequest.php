<?php

namespace App\Http\Requests\Distributor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'penanggung_jawab' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z\s.\'-]+$/'],
            'telepon' => ['nullable', 'string', 'regex:/^[0-9]{10,13}$/'],
            'alamat_kantor' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.,\/\'-]+$/'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'penanggung_jawab.regex' => 'Nama penanggung jawab hanya boleh berisi huruf, spasi, titik, apostrof, dan tanda hubung.',
            'telepon.regex' => 'Nomor telepon hanya boleh berisi angka (10 sampai 13 digit).',
            'alamat_kantor.regex' => 'Alamat kantor hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/\'-).',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
        ];
    }
}
