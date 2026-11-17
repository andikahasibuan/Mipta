<?php

namespace App\Http\Requests\Admin;

use App\Models\RecipientStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecipientStoreRequest extends FormRequest
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
        /** @var RecipientStore $store */
        $store = $this->route('store');

        return [
            'market_id' => ['required', 'integer', 'exists:markets,id'],
            'distributor_id' => ['nullable', 'integer', 'exists:distributors,id'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'],
            'alamat' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9 .,\/-]+$/'],
            'nib' => ['required', 'digits:13', Rule::unique('recipient_stores', 'nib')->ignore($store->id)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Nama toko hanya boleh berisi huruf, angka, dan spasi.',
            'alamat.regex' => 'Detail alamat hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/-).',
            'nib.digits' => 'NIB harus terdiri dari tepat 13 angka.',
            'nib.unique' => 'NIB sudah digunakan toko lain.',
        ];
    }
}
