@php
    $isEdit = $report !== null;
    $selectedStoreId = (int) old('recipient_store_id', $report?->recipient_store_id);
    $selectedStore = $stores->firstWhere('id', $selectedStoreId);
    $formAction = $isEdit
        ? route('distributor.distribution-reports.update', $report)
        : route('distributor.distribution-reports.store');
    $cancelUrl = $isEdit
        ? route('distributor.report-history.index')
        : route('distributor.dashboard');
@endphp

<div class="min-w-0">
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="min-w-0 lg:col-span-2">
            <div class="flex min-h-[34rem] flex-col overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Laporan Penyaluran</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Lengkapi data sesuai catatan penyaluran. Kolom bertanda * wajib diisi.
                    </p>
                </div>

                @if ($isEdit)
                    <div class="mx-5 mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        Laporan ini sudah terkirim. Perubahan yang disimpan akan langsung memperbarui laporan.
                    </div>
                @endif

                @if ($stores->isEmpty())
                    <div class="mx-5 mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        Anda belum memiliki data toko penerima.
                        <a href="{{ route('distributor.stores.create') }}" class="font-semibold underline">
                            Tambah toko terlebih dahulu
                        </a>.
                    </div>
                @endif

                <form method="POST" action="{{ $formAction }}" class="flex-1 px-6 py-7">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif
                    <div class="mt-3 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="distribution_date" class="block text-sm font-medium text-slate-700">
                                Tanggal Penyaluran <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="distribution_date"
                                name="distribution_date"
                                type="date"
                                value="{{ old('distribution_date', $report?->distribution_date?->format('Y-m-d') ?? now()->toDateString()) }}"
                                max="{{ now()->toDateString() }}"
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                            >
                            @error('distribution_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="recipient_store_id" class="block text-sm font-medium text-slate-700">
                                Nama Toko <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="recipient_store_id"
                                name="recipient_store_id"
                                class="mt-1 w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                            >
                                <option value="">Pilih toko penerima</option>
                                @foreach ($stores as $store)
                                    <option
                                        value="{{ $store->id }}"
                                        data-market="{{ $store->market->name }}"
                                        data-alamat="{{ $store->alamat ?? '' }}"
                                        data-nib="{{ $store->nib ?? '' }}"
                                        @selected($selectedStoreId === $store->id)
                                    >
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('recipient_store_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-5 grid gap-5 sm:grid-cols-3">
                        <div>
                            <label for="store-market" class="block text-sm font-medium text-slate-700">Pasar</label>
                            <input
                                id="store-market"
                                type="text"
                                readonly
                                value="{{ $selectedStore?->market?->name }}"
                                placeholder="Terisi otomatis"
                                class="mt-1 w-full cursor-default rounded-lg border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm text-slate-600 placeholder:text-slate-400"
                            >
                        </div>

                        <div>
                            <label for="store-alamat" class="block text-sm font-medium text-slate-700">Alamat</label>
                            <input
                                id="store-alamat"
                                type="text"
                                readonly
                                value="{{ $selectedStore?->alamat }}"
                                placeholder="Terisi otomatis"
                                class="mt-1 w-full cursor-default rounded-lg border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm text-slate-600 placeholder:text-slate-400"
                            >
                        </div>

                        <div>
                            <label for="store-nib" class="block text-sm font-medium text-slate-700">NIB</label>
                            <input
                                id="store-nib"
                                type="text"
                                readonly
                                value="{{ $selectedStore?->nib }}"
                                placeholder="Terisi otomatis"
                                class="mt-1 w-full cursor-default rounded-lg border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm text-slate-600 placeholder:text-slate-400"
                            >
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="cartons" class="block text-sm font-medium text-slate-700">
                            Jumlah Penyaluran (karton) <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="cartons"
                            name="cartons"
                            type="number"
                            min="1"
                            value="{{ old('cartons', $report?->cartons) }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                        >
                        @error('cartons')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-5">
                        <label for="notes" class="block text-sm font-medium text-slate-700">
                            Keterangan
                        </label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            maxlength="120"
                            placeholder="Tambahkan keterangan penyaluran (opsional)..."
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                        >{{ old('notes', $report?->notes) }}</textarea>
                        <p class="mt-1 text-xs text-slate-500">Maksimal 120 karakter.</p>
                        @error('notes')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-5">
                        <a
                            href="{{ $cancelUrl }}"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            Batal
                        </a>

                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="submit"
                                @disabled($stores->isEmpty())
                                class="cursor-pointer rounded-lg bg-[#018F98] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#018F98]/90 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{ $isEdit ? 'Simpan Perubahan' : 'Kirim Laporan' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <aside class="min-w-0 self-start lg:col-span-1">
            <div class="rounded-xl border border-slate-200 bg-[#E0F2F3] p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-[#018F98]">
                        <x-heroicon-o-document-text class="h-5 w-5" />
                    </span>
                    <h3 class="text-base font-semibold text-[#016B72]">Panduan pengisian</h3>
                </div>

                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-snug text-[#018F98]">
                    <li>Pastikan toko penerima yang sudah terdaftar.</li>
                    <li>Alamat, NIB, dan pasar akan terisi otomatis.</li>
                    <li>Jumlah penyaluran dicatat dalam karton.</li>
                </ul>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const storeSelect = document.getElementById('recipient_store_id');
            const marketInput = document.getElementById('store-market');
            const alamatInput = document.getElementById('store-alamat');
            const nibInput = document.getElementById('store-nib');

            if (! storeSelect || ! marketInput || ! alamatInput || ! nibInput) {
                return;
            }

            const syncStoreFields = () => {
                const option = storeSelect.selectedOptions[0];

                marketInput.value = option?.dataset.market ?? '';
                alamatInput.value = option?.dataset.alamat ?? '';
                nibInput.value = option?.dataset.nib ?? '';
            };

            storeSelect.addEventListener('change', syncStoreFields);
            syncStoreFields();
        });
    </script>
@endpush
