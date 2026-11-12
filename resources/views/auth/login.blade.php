@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <div class="text-center">
        <img
            src="{{ asset('images/mipta-logo.png') }}"
            alt="MIPTA"
            class="mx-auto block w-28"
        >
        <h1 class="mt-2 text-2xl font-semibold text-[#018F98]">Selamat Datang</h1>
        <p class="mt-1 text-sm text-slate-500">Silahkan masuk menggunakan akun yang telah terdaftar</p>
    </div>

    @if ($selectedContributor)
        <div class="mt-5 space-y-2 rounded-lg border border-[#B3E5E8] bg-[#E6F4F5] px-5 py-4 text-left">
            <p class="text-sm font-bold uppercase tracking-wide text-[#016B72]">Identitas dipilih</p>
            <p class="text-base font-semibold text-[#016B72]">{{ $selectedContributor['name'] }}</p>
            <p class="text-sm text-slate-500">Masukkan kata sandi akun tersebut untuk melanjutkan.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 flex flex-col gap-4">
        @csrf

        <div>
            <label for="identifier" class="block text-sm font-medium text-slate-700">Email atau Username</label>
            <input
                id="identifier"
                name="identifier"
                type="text"
                value="{{ old('identifier', $selectedContributor['username'] ?? '') }}"
                placeholder="Masukkan email atau username"
                required
                @if (! $selectedContributor) autofocus @endif
                autocomplete="username"
                @class([
                    'mt-1 block w-full rounded-md border px-3 py-2 text-sm shadow-[0_2px_8px_rgba(15,23,42,0.12)] focus:outline-none focus:ring-2',
                    'border-slate-300 focus:border-[#018F98] focus:ring-[#B3E5E8]' => ! $errors->has('identifier'),
                    'border-red-400 focus:border-red-500 focus:ring-red-200' => $errors->has('identifier'),
                ])
            >
            @error('identifier')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                placeholder="Masukkan kata sandi"
                required
                @if ($selectedContributor) autofocus @endif
                autocomplete="current-password"
                @class([
                    'mt-1 block w-full rounded-md border px-3 py-2 text-sm shadow-[0_2px_8px_rgba(15,23,42,0.12)] focus:outline-none focus:ring-2',
                    'border-slate-300 focus:border-[#018F98] focus:ring-[#B3E5E8]' => ! $errors->has('password'),
                    'border-red-400 focus:border-red-500 focus:ring-red-200' => $errors->has('password'),
                ])
            >
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label for="remember" class="flex items-center gap-2 text-sm text-slate-600">
                <input
                    id="remember"
                    name="remember"
                    type="checkbox"
                    value="1"
                    @checked(old('remember'))
                    class="h-4 w-4 rounded border-slate-300 text-[#018F98] focus:ring-2 focus:ring-[#7DD3D8]"
                >
                Ingat saya
            </label>

            <span class="text-sm font-medium text-[#018F98]">Lupa kata sandi?</span>
        </div>

        <button
            type="submit"
            class="mt-2 w-full cursor-pointer rounded-md bg-[#018F98] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#017A81] focus:outline-none focus:ring-2 focus:ring-[#018F98]/50"
        >
            Masuk
        </button>
    </form>
@endsection
