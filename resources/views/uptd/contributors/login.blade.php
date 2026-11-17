@extends('layouts.portal')

@section('title', 'Masuk Kontributor UPTD')

@section('content')
    <header class="w-full border-b border-slate-200 bg-white">
        <div class="flex w-full items-center justify-between gap-4 px-6 py-4 lg:px-8">
            <img
                src="{{ asset('images/mipta-logo.png') }}"
                alt="MIPTA"
                class="h-10 w-10 shrink-0 object-contain"
            >
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-red-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/50"
                >
                    <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4" />
                    Keluar
                </button>
            </form>
        </div>
    </header>

    <main class="flex min-h-[calc(100dvh-73px)] w-full items-center justify-center px-4 pb-20 pt-6 sm:px-6 sm:pb-24 sm:pt-8">
        <section class="w-full max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_2px_12px_rgba(15,23,42,0.06)]">
            <div class="border-b border-slate-100 px-5 py-4 text-center">
                <img
                    src="{{ asset('images/mipta-logo.png') }}"
                    alt="MIPTA"
                    class="mx-auto block w-28"
                >
            </div>

            <div class="px-5 pb-5 pt-4">
                <div class="rounded-lg border border-[#B3E5E8] bg-[#E0F2F3] px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-[#018F98]">Identitas dipilih</p>
                    <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ $contributor->name }}</p>
                    <p class="mt-0.5 truncate text-xs text-slate-600">{{ $contributor->market?->name }}</p>
                </div>

                <form
                    method="POST"
                    action="{{ route('uptd.contributors.login.store', $contributor) }}"
                    class="mt-4 flex flex-col gap-3.5"
                >
                    @csrf

                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700">Username</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            placeholder="Masukkan username"
                            required
                            autofocus
                            autocomplete="username"
                            autocapitalize="none"
                            spellcheck="false"
                            @class([
                                'mt-1 block w-full rounded-md border px-3 py-2 text-sm shadow-[0_2px_8px_rgba(15,23,42,0.12)] focus:outline-none focus:ring-2',
                                'border-slate-300 focus:border-[#018F98] focus:ring-[#B3E5E8]' => ! $errors->has('username'),
                                'border-red-400 focus:border-red-500 focus:ring-red-200' => $errors->has('username'),
                            ])
                        >
                        @error('username')
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
                        class="w-full cursor-pointer rounded-md bg-[#018F98] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#017A81] focus:outline-none focus:ring-2 focus:ring-[#018F98]/50"
                    >
                        Masuk
                    </button>

                    <a
                        href="{{ route('uptd.contributors.index') }}"
                        class="text-center text-sm font-medium text-[#018F98] transition hover:underline"
                    >
                        Kembali pilih identitas
                    </a>
                </form>
            </div>
        </section>
    </main>
@endsection
