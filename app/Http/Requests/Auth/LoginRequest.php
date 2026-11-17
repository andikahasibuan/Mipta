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
     * Jumlah maksimal percobaan gagal dari satu IP untuk akun yang tidak terdaftar.
     */
    private const UNKNOWN_ACCOUNT_IP_MAX_ATTEMPTS = 3;

    /**
     * Jumlah maksimal percobaan gagal dari satu IP untuk akun yang valid.
     */
    private const ACCOUNT_IP_MAX_ATTEMPTS = 20;

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
            RateLimiter::hit(
                $user === null ? $this->unknownIpThrottleKey() : $this->accountIpThrottleKey(),
                self::LOCKOUT_SECONDS,
            );

            throw ValidationException::withMessages([
                'identifier' => 'Email/username atau password salah.',
            ]);
        }

        if (! Auth::attempt([$field => $identifier, 'password' => $password, 'is_active' => true])) {
            RateLimiter::hit($this->accountThrottleKey($user, $identifier), self::LOCKOUT_SECONDS);
            RateLimiter::hit(
                $user === null ? $this->unknownIpThrottleKey() : $this->accountIpThrottleKey(),
                self::LOCKOUT_SECONDS,
            );

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
        RateLimiter::clear($this->accountIpThrottleKey());
    }

    /**
     * Pastikan permintaan login belum melewati batas percobaan.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(?User $user, string $identifier): void
    {
        $checks = [
            [$this->accountThrottleKey($user, $identifier), self::MAX_ATTEMPTS],
            [$this->unknownIpThrottleKey(), self::UNKNOWN_ACCOUNT_IP_MAX_ATTEMPTS],
        ];

        if ($user !== null) {
            $checks[] = [$this->accountIpThrottleKey(), self::ACCOUNT_IP_MAX_ATTEMPTS];
        }

        foreach ($checks as [$key, $maxAttempts]) {
            if (! RateLimiter::tooManyAttempts($key, $maxAttempts)) {
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
     * Kunci pembatas percobaan untuk IP klien dengan akun tidak terdaftar.
     */
    private function unknownIpThrottleKey(): string
    {
        return 'login-ip|'.$this->ip();
    }

    /**
     * Kunci pembatas percobaan IP untuk akun yang valid.
     */
    private function accountIpThrottleKey(): string
    {
        return 'login-ip-account|'.$this->ip();
    }
}
