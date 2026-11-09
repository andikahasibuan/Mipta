@extends('layouts.admin')

@section('title', 'Tambah Distributor')

@section('content')
    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('admin.distributors.store') }}" class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            @csrf

            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Tambah distributor</h2>
            </div>

            <div class="space-y-4 px-5 py-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Nama Perusahaan Distributor</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        placeholder="Contoh: PT Amanah Niaga Mandiri"
                        required
                        pattern="[A-Za-z0-9 .'-]+"
                        title="Nama perusahaan hanya boleh berisi huruf, angka, spasi, titik, apostrof, dan tanda hubung."
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="penanggung_jawab" class="block text-sm font-medium text-slate-700">Nama Penanggung Jawab</label>
                    <input
                        id="penanggung_jawab"
                        name="penanggung_jawab"
                        type="text"
                        value="{{ old('penanggung_jawab') }}"
                        placeholder="Contoh: Budi Santoso"
                        required
                        pattern="[A-Za-z .'-]+"
                        title="Nama penanggung jawab hanya boleh berisi huruf, spasi, titik, apostrof, dan tanda hubung."
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                    >
                    @error('penanggung_jawab')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="telepon" class="block text-sm font-medium text-slate-700">No. Handphone Penanggung Jawab</label>
                    <input
                        id="telepon"
                        name="telepon"
                        type="tel"
                        value="{{ old('telepon') }}"
                        placeholder="Contoh: 081234567890"
                        required
                        inputmode="numeric"
                        minlength="10"
                        maxlength="15"
                        pattern="[0-9]{10,15}"
                        title="No. handphone hanya boleh berisi angka (10 sampai 15 digit)."
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                    >
                    @error('telepon')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email Perusahaan</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="Contoh: admin@amanahniaga.co.id"
                        required
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Minimal 8 karakter"
                        required
                        minlength="8"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                    >
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
                    class="rounded-lg bg-[#2D507E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#2D507E]/90"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
@endsection
