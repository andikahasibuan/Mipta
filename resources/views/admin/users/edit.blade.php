@extends('layouts.admin')

@section('title', 'Ubah Pengguna')

@section('page-title', 'Ubah Pengguna')

@section('content')
    <div class="max-w-3xl">
        <a
            href="{{ route('admin.users.index') }}"
            class="inline-flex items-center gap-1 text-sm text-slate-500 transition hover:text-slate-700"
        >
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            Kembali ke daftar pengguna
        </a>

        <form
            method="POST"
            action="{{ route('admin.users.update', $user) }}"
            class="mt-4 rounded-xl border border-slate-200 bg-white p-5 lg:p-6"
        >
            @csrf
            @method('PUT')

            <h2 class="text-lg font-semibold text-slate-900">Data pengguna</h2>
            <p class="mt-1 text-sm text-slate-500">Ubah data akun {{ $user->name }}.</p>

            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        autofocus
                        class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30 @error('name') border-rose-400 focus:border-rose-500 @else border-slate-300 focus:border-[#5191E4] @enderror"
                    >
                    @error('name')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="{{ old('username', $user->username) }}"
                        required
                        class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30 @error('username') border-rose-400 focus:border-rose-500 @else border-slate-300 focus:border-[#5191E4] @enderror"
                    >
                    @error('username')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30 @error('email') border-rose-400 focus:border-rose-500 @else border-slate-300 focus:border-[#5191E4] @enderror"
                    >
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-slate-700">Peran</label>
                    <select
                        id="role"
                        name="role"
                        required
                        class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30 @error('role') border-rose-400 focus:border-rose-500 @else border-slate-300 focus:border-[#5191E4] @enderror"
                    >
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role', $user->role->value) === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="block text-sm font-medium text-slate-700">Status Akun</span>
                    <div class="mt-2 flex items-center gap-5">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                            <input
                                type="radio"
                                name="is_active"
                                value="1"
                                @checked((string) old('is_active', $user->is_active ? '1' : '0') === '1')
                                class="h-4 w-4 border-slate-300 text-[#2D507E] focus:ring-[#5191E4]"
                            >
                            Aktif
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                            <input
                                type="radio"
                                name="is_active"
                                value="0"
                                @checked((string) old('is_active', $user->is_active ? '1' : '0') === '0')
                                class="h-4 w-4 border-slate-300 text-[#2D507E] focus:ring-[#5191E4]"
                            >
                            Nonaktif
                        </label>
                    </div>
                    @error('is_active')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password Baru</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30 @error('password') border-rose-400 focus:border-rose-500 @else border-slate-300 focus:border-[#5191E4] @enderror"
                    >
                    <p class="mt-1.5 text-xs text-slate-500">Kosongkan jika tidak ingin mengubah password.</p>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Password Baru</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                    >
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-5">
                <a
                    href="{{ route('admin.users.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#2D507E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#2D507E]/90"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
