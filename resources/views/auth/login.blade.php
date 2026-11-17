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

    <form method="POST" action="{{ route('login') }}" class="mt-6 flex flex-col gap-4">
        @csrf

        <div>
            <label for="identifier" class="block text-sm font-medium text-slate-700">Email atau Username</label>
            <input
                id="identifier"
                name="identifier"
                type="text"
                value="{{ old('identifier') }}"
                placeholder="Masukkan email atau username"
                required
                autofocus
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
            <x-ui.password-input
                id="password"
                name="password"
                placeholder="Masukkan kata sandi"
                required
                autocomplete="current-password"
                class="mt-1 block w-full rounded-md border px-3 py-2 pr-10 text-sm shadow-[0_2px_8px_rgba(15,23,42,0.12)] focus:outline-none focus:ring-2 {{ $errors->has('password') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 focus:border-[#018F98] focus:ring-[#B3E5E8]' }}"
            />
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end">
            <span class="text-sm font-medium text-[#018F98]">Hubungi Admin</span>
        </div>

        <button
            type="submit"
            class="mt-2 w-full cursor-pointer rounded-md bg-[#018F98] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#017A81] focus:outline-none focus:ring-2 focus:ring-[#018F98]/50"
        >
            Masuk
        </button>
    </form>
@endsection
