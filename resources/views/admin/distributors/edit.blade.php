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
                    <label for="name" class="block text-sm font-medium text-slate-700">Nama Distributor</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $distributor->name) }}"
                        placeholder="Contoh: PT Amanah Niaga Mandiri"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="wilayah" class="block text-sm font-medium text-slate-700">Wilayah</label>
                    <input
                        id="wilayah"
                        name="wilayah"
                        type="text"
                        value="{{ old('wilayah', $distributor->wilayah) }}"
                        placeholder="Contoh: Padang"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                    >
                    @error('wilayah')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2.5">
                    <input
                        name="is_active"
                        type="checkbox"
                        value="1"
                        @checked(old('is_active', $distributor->is_active))
                        class="h-4 w-4 rounded border-slate-300 text-[#2D507E] focus:ring-[#5191E4]"
                    >
                    <span class="text-sm text-slate-700">Status aktif</span>
                </label>
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
