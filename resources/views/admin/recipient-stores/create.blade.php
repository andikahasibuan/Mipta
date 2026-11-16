@extends('layouts.admin')

@section('title', 'Tambah Toko Penerima')

@section('content')
    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('admin.recipient-stores.store') }}" class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            @csrf

            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Tambah toko penerima</h2>
            </div>

            <div class="space-y-4 px-5 py-5">
                <div>
                    <label for="market_id" class="block text-sm font-medium text-slate-700">Pasar</label>
                    <select
                        id="market_id"
                        name="market_id"
                        class="mt-1 w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                    >
                        <option value="">Pilih pasar</option>
                        @foreach ($markets as $market)
                            <option value="{{ $market->id }}" @selected(old('market_id') == $market->id)>{{ $market->name }}</option>
                        @endforeach
                    </select>
                    @error('market_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="distributor_id" class="block text-sm font-medium text-slate-700">Distributor</label>
                    <select
                        id="distributor_id"
                        name="distributor_id"
                        class="mt-1 w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                    >
                        <option value="">— Belum ditentukan —</option>
                        @foreach ($distributors as $distributor)
                            <option value="{{ $distributor->id }}" @selected(old('distributor_id') == $distributor->id)>{{ $distributor->name }}</option>
                        @endforeach
                    </select>
                    @error('distributor_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Nama Toko <span class="text-red-500">*</span></label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        required
                        pattern="[A-Za-z0-9 ]+"
                        title="Nama toko hanya boleh berisi huruf, angka, dan spasi."
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="alamat" class="block text-sm font-medium text-slate-700">Alamat (opsional)</label>
                    <input
                        id="alamat"
                        name="alamat"
                        type="text"
                        value="{{ old('alamat') }}"
                        pattern="[A-Za-z0-9 .,/-]+"
                        title="Detail alamat hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/-)."
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                    >
                    <p class="mt-1 text-xs text-slate-500">Tambahkan detail alamat bila diperlukan.</p>
                    @error('alamat')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nib" class="block text-sm font-medium text-slate-700">NIB <span class="text-red-500">*</span></label>
                    <input
                        id="nib"
                        name="nib"
                        type="text"
                        value="{{ old('nib') }}"
                        required
                        inputmode="numeric"
                        minlength="13"
                        maxlength="13"
                        pattern="[0-9]{13}"
                        title="NIB harus terdiri dari tepat 13 angka."
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                    >
                    @error('nib')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2.5">
                    <input
                        name="is_active"
                        type="checkbox"
                        value="1"
                        @checked(old('is_active', true))
                        class="h-4 w-4 rounded border-slate-300 text-[#018F98] focus:ring-[#018F98]"
                    >
                    <span class="text-sm text-slate-700">Status aktif</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-5 py-4">
                <a
                    href="{{ route('admin.recipient-stores.index') }}"
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
