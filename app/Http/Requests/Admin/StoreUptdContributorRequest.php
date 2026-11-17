<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreUptdContributorRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.\'-]+$/'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9._-]+$/', Rule::unique('uptd_contributors', 'username')],
            'password' => ['required', 'string', 'min:8'],
            'market_id' => [
                'required',
                'integer',
                Rule::exists('markets', 'id'),
                Rule::unique('uptd_contributors', 'market_id'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Nama hanya boleh berisi huruf, angka, spasi, titik, apostrof, dan tanda hubung.',
            'username.regex' => 'Username tidak boleh mengandung spasi dan hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',
            'username.unique' => 'Username sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'market_id.required' => 'Pasar terkait wajib dipilih.',
            'market_id.exists' => 'Pasar yang dipilih tidak valid.',
            'market_id.unique' => 'Pasar ini sudah memiliki kontributor UPTD.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['username' => Str::lower($this->string('username')->toString())]);
    }
}
