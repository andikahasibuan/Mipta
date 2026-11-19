<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^(?=.*[A-Za-z])[A-Za-z0-9\s.\'-]+$/'],
            'username' => ['required', 'string', 'max:255', 'regex:/^(?=.*[a-z])[a-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::enum(Role::class), Rule::notIn([Role::Distributor->value])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Nama harus mengandung huruf dan hanya boleh berisi huruf, angka, spasi, titik, apostrof, dan tanda hubung.',
            'username.regex' => 'Username harus mengandung huruf, tanpa spasi, dan hanya boleh berisi huruf kecil, angka, titik, garis bawah, dan tanda hubung.',
            'username.unique' => 'Username sudah digunakan.',
            'email.unique' => 'Email sudah digunakan.',
            'role.not_in' => 'Akun distributor dikelola melalui menu Distributor.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['username' => Str::lower($this->string('username')->toString())]);
    }
}
