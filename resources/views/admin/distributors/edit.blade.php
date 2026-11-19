@extends('layouts.admin')

@section('title', 'Edit Distributor')

@section('content')
    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('admin.distributors.update', $distributor) }}" class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            @csrf
            @method('PUT')

            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Edit distributor</h2>
            </div>

            <div class="space-y-4 px-5 py-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Distributor</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $distributor->name) }}"
                        required
                        pattern="(?=.*[A-Za-z])[A-Za-z0-9 .'-]+"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700">Username</label>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        value="{{ old('username', $account?->username) }}"
                        required
                        pattern="(?=.*[a-z])[a-z0-9._-]+"
                        autocapitalize="none"
                        spellcheck="false"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                    >
                    @error('username')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="penanggung_jawab" class="block text-sm font-medium text-slate-700">Penanggung Jawab</label>
                    <input
                        id="penanggung_jawab"
                        name="penanggung_jawab"
                        type="text"
                        value="{{ old('penanggung_jawab', $distributor->penanggung_jawab) }}"
                        required
                        pattern="(?=.*[A-Za-z])[A-Za-z0-9 .'-]+"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                    >
                    @error('penanggung_jawab')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="telepon" class="block text-sm font-medium text-slate-700">No HP Penanggung Jawab</label>
                    <input
                        id="telepon"
                        name="telepon"
                        type="tel"
                        value="{{ old('telepon', $distributor->telepon) }}"
                        required
                        inputmode="numeric"
                        minlength="10"
                        maxlength="13"
                        pattern="[0-9]{10,13}"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                    >
                    @error('telepon')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email (opsional)</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $account?->email) }}"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="alamat_kantor" class="block text-sm font-medium text-slate-700">Alamat (opsional)</label>
                    <input
                        id="alamat_kantor"
                        name="alamat_kantor"
                        type="text"
                        value="{{ old('alamat_kantor', $distributor->alamat_kantor) }}"
                        pattern="(?=.*[A-Za-z])[A-Za-z0-9 .,/'-]+"
                        title="Alamat hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/'-)."
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                    >
                    @error('alamat_kantor')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

     

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <x-ui.password-input
                        id="password"
                        name="password"
                        minlength="8"
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                    />
                    <p class="mt-1 text-xs text-slate-500">Kosongkan jika tidak ingin mengubah password.</p>
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-5 py-4">
                <a
                    href="{{ route('admin.distributors.index') }}"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="rounded-lg bg-[#018F98] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#018F98]/90"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
@endsection
