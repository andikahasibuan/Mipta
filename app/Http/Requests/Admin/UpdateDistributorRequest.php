<?php

namespace App\Http\Requests\Admin;

use App\Models\Distributor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateDistributorRequest extends FormRequest
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
        /** @var Distributor $distributor */
        $distributor = $this->route('distributor');
        $account = $distributor->account;

        $emailRule = Rule::unique('users', 'email');
        $usernameRule = Rule::unique('users', 'username');

        if ($account !== null) {
            $emailRule->ignore($account->id);
            $usernameRule->ignore($account->id);
        }

        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^(?=.*[A-Za-z])[A-Za-z0-9\s.\'-]+$/', Rule::unique('distributors', 'name')->ignore($distributor->id)],
            'username' => ['required', 'string', 'max:255', 'regex:/^(?=.*[a-z])[a-z0-9._-]+$/', $usernameRule],
            'penanggung_jawab' => ['required', 'string', 'max:255', 'regex:/^(?=.*[A-Za-z])[A-Za-z0-9\s.\'-]+$/'],
            'telepon' => ['required', 'string', 'regex:/^[0-9]{10,13}$/'],
            'alamat_kantor' => ['nullable', 'string', 'max:255', 'regex:/^(?=.*[A-Za-z])[A-Za-z0-9\s.,\/\'-]+$/'],
            'email' => ['nullable', 'string', 'email', 'max:255', $emailRule],
            'password' => [$account === null ? 'required' : 'nullable', 'string', 'min:8'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama perusahaan distributor wajib diisi.',
            'name.regex' => 'Nama perusahaan harus mengandung huruf dan hanya boleh berisi huruf, angka, spasi, titik, apostrof, dan tanda hubung.',
            'name.unique' => 'Nama distributor sudah digunakan.',
            'username.required' => 'Username distributor wajib diisi.',
            'username.regex' => 'Username harus mengandung huruf, tanpa spasi, dan hanya boleh berisi huruf kecil, angka, titik, garis bawah, dan tanda hubung.',
            'username.unique' => 'Username sudah digunakan.',
            'penanggung_jawab.required' => 'Nama penanggung jawab wajib diisi.',
            'penanggung_jawab.regex' => 'Nama penanggung jawab harus mengandung huruf dan hanya boleh berisi huruf, angka, spasi, titik, apostrof, dan tanda hubung.',
            'telepon.required' => 'No. handphone penanggung jawab wajib diisi.',
            'telepon.regex' => 'No. handphone hanya boleh berisi angka (10 sampai 13 digit).',
            'alamat_kantor.regex' => 'Alamat harus mengandung huruf dan hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/\'-).',
            'email.unique' => 'Email perusahaan sudah digunakan akun lain.',
            'password.required' => 'Password wajib diisi untuk membuat akun distributor.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['username' => Str::lower($this->string('username')->toString())]);
    }
}
