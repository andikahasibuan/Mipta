<?php

namespace App\Http\Requests\Admin;

use App\Models\Market;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMarketRequest extends FormRequest
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
        /** @var Market $market */
        $market = $this->route('market');

        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.\'-]+$/', Rule::unique('markets', 'name')->ignore($market->id)],
            'lokasi' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.,\/\'-]+$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Nama pasar hanya boleh berisi huruf, angka, spasi, titik, apostrof, dan tanda hubung.',
            'name.unique' => 'Nama pasar sudah digunakan.',
            'lokasi.regex' => 'Lokasi hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/\'-).',
        ];
    }
}
