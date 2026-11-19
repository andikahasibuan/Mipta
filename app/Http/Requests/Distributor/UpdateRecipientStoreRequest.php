<?php

namespace App\Http\Requests\Distributor;

use App\Models\RecipientStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRecipientStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $store = $this->route('store');

        abort_unless($store instanceof RecipientStore && Gate::allows('update', $store), 404);

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
            'name' => ['required', 'string', 'max:255', 'regex:/^(?=.*[A-Za-z])[A-Za-z0-9\s.\'-]+$/'],
            'alamat' => ['nullable', 'string', 'max:255', 'regex:/^(?=.*[A-Za-z])[A-Za-z0-9\s.,\/\'-]+$/'],
            'nib' => ['required', 'digits:13', Rule::unique('recipient_stores', 'nib')->ignore($store->id)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Nama toko harus mengandung huruf dan hanya boleh berisi huruf, angka, spasi, titik, apostrof, dan tanda hubung.',
            'alamat.regex' => 'Detail alamat harus mengandung huruf dan hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/\'-).',
            'nib.digits' => 'NIB harus terdiri dari tepat 13 angka.',
            'nib.unique' => 'NIB sudah digunakan toko lain.',
        ];
    }
}
