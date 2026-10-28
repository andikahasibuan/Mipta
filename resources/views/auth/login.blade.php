@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <div class="text-center">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-950">MIPTA</p>
        <h1 class="mt-2 text-2xl font-semibold text-blue-600">Selamat Datang</h1>
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
                required
                autofocus
                autocomplete="username"
                @class([
                    'mt-1 block w-full rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2',
                    'border-slate-300 focus:border-blue-500 focus:ring-blue-200' => ! $errors->has('identifier'),
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
                required
                autocomplete="current-password"
                @class([
                    'mt-1 block w-full rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2',
                    'border-slate-300 focus:border-blue-500 focus:ring-blue-200' => ! $errors->has('password'),
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
                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-300"
                >
                Ingat saya
            </label>

            <span class="text-sm font-medium text-blue-600">Lupa kata sandi?</span>
        </div>

        <button
            type="submit"
            class="mt-2 w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300"
        >
            Masuk
        </button>
    </form>
@endsection
