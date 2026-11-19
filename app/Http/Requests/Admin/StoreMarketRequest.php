<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMarketRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'regex:/^(?=.*[A-Za-z])[A-Za-z0-9\s.\'-]+$/', Rule::unique('markets', 'name')],
            'lokasi' => ['required', 'string', 'max:255', 'regex:/^(?=.*[A-Za-z])[A-Za-z0-9\s.,\/\'-]+$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Nama pasar harus mengandung huruf dan hanya boleh berisi huruf, angka, spasi, titik, apostrof, dan tanda hubung.',
            'name.unique' => 'Nama pasar sudah digunakan.',
            'lokasi.regex' => 'Lokasi harus mengandung huruf dan hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/\'-).',
        ];
    }
}
