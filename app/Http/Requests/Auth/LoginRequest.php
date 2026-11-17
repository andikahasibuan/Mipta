<?php

namespace App\Http\Requests\Auth;

use App\Enums\Role;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Jumlah maksimal percobaan login gagal sebelum dikunci.
     */
    private const MAX_ATTEMPTS = 3;

    /**
     * Durasi penguncian login dalam detik (10 menit).
     */
    private const LOCKOUT_SECONDS = 600;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
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
            'identifier.required' => 'Email atau username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ];
    }

    /**
     * Coba autentikasi pengguna memakai email atau username.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $identifier = trim((string) $this->string('identifier'));
        $password = (string) $this->string('password');
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($field, $identifier)->first();

        $this->ensureIsNotRateLimited($user, $identifier);

        if ($this->isUptdContributorLogin($field, $identifier, $user)) {
            RateLimiter::hit($this->accountThrottleKey($user, $identifier), self::LOCKOUT_SECONDS);
            RateLimiter::hit($this->ipThrottleKey(), self::LOCKOUT_SECONDS);

            throw ValidationException::withMessages([
                'identifier' => 'Akun kontributor UPTD harus masuk melalui akun bersama UPTD.',
            ]);
        }

        if (! Auth::attempt([$field => $identifier, 'password' => $password, 'is_active' => true])) {
            RateLimiter::hit($this->accountThrottleKey($user, $identifier), self::LOCKOUT_SECONDS);

            if ($user === null) {
                RateLimiter::hit($this->ipThrottleKey(), self::LOCKOUT_SECONDS);
            }

            if ($user && ! $user->is_active && Hash::check($password, $user->password)) {
                throw ValidationException::withMessages([
                    'identifier' => 'Akun ini dinonaktifkan. Hubungi administrator.',
                ]);
            }

            throw ValidationException::withMessages([
                'identifier' => 'Email/username atau password salah.',
            ]);
        }

        RateLimiter::clear($this->accountThrottleKey($user, $identifier));
    }

    /**
     * Pastikan permintaan login belum melewati batas percobaan.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(?User $user, string $identifier): void
    {
        foreach ([$this->accountThrottleKey($user, $identifier), $this->ipThrottleKey()] as $key) {
            if (! RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
                continue;
            }

            event(new Lockout($this));

            $seconds = RateLimiter::availableIn($key);
            $minutes = max(1, (int) ceil($seconds / 60));

            throw ValidationException::withMessages([
                'identifier' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$minutes} menit.",
            ]);
        }
    }

    /**
     * Periksa apakah percobaan login memakai akun kontributor UPTD yang tidak boleh masuk langsung.
     */
    private function isUptdContributorLogin(string $field, string $identifier, ?User $user): bool
    {
        if ($user !== null && $user->role === Role::UptdPasar && ! $user->isSharedUptdAccount()) {
            return true;
        }

        return $field === 'username'
            && UptdContributor::query()->where('username', Str::lower($identifier))->exists();
    }

    /**
     * Kunci pembatas percobaan untuk akun yang sedang dicoba.
     */
    private function accountThrottleKey(?User $user, string $identifier): string
    {
        if ($user !== null) {
            return 'login-account|'.$user->getKey();
        }

        return 'login-identifier|'.Str::transliterate(Str::lower($identifier));
    }

    /**
     * Kunci pembatas percobaan untuk IP klien.
     */
    private function ipThrottleKey(): string
    {
        return 'login-ip|'.$this->ip();
    }
}
