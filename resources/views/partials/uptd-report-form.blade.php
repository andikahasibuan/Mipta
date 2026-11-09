@php
    $isEdit = $report !== null;
    $formAction = $isEdit
        ? route('uptd.reports.update', $report)
        : route('uptd.reports.store');
    $cancelAction = $isEdit
        ? route('uptd.reports.index')
        : route('uptd.dashboard');
@endphp

<div class="grid gap-4 lg:grid-cols-3">
    <div class="min-w-0 lg:col-span-2">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">Formulir kondisi lapangan</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Identitas dan pasar terhubung otomatis dari sesi kontributor aktif.
                </p>
            </div>

            <form method="POST" action="{{ $formAction }}" class="px-6 py-7">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="rounded-lg border border-[#D6E3F2] bg-[#E8F0FB] px-4 py-4">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-[#2D507E]/70">
                            Identitas Kontributor
                        </p>
                        <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ $contributor->name }}</p>
                        <p class="mt-0.5 text-xs text-[#2D507E]">Terisi otomatis</p>
                    </div>

                    <div class="rounded-lg border border-[#D6E3F2] bg-[#E8F0FB] px-4 py-4">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-[#2D507E]/70">
                            Pasar yang Ditugaskan
                        </p>
                        <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ $contributor->market?->name ?? '—' }}</p>
                        <p class="mt-0.5 text-xs text-[#2D507E]">Tidak dapat diubah</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="report_date" class="block text-sm font-medium text-slate-700">
                            Tanggal Laporan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="report_date"
                            name="report_date"
                            type="date"
                            value="{{ old('report_date', $report?->report_date?->format('Y-m-d') ?? now()->toDateString()) }}"
                            max="{{ now()->toDateString() }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                        >
                        @error('report_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="condition" class="block text-sm font-medium text-slate-700">
                            Label Kondisi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative mt-1">
                            <select
                                id="condition"
                                name="condition"
                                class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3 pr-10 text-sm text-slate-800 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                            >
                                @foreach ($conditions as $condition)
                                    <option
                                        value="{{ $condition->value }}"
                                        @selected(old('condition', $report?->condition?->value ?? \App\Enums\FieldCondition::Normal->value) === $condition->value)
                                    >
                                        {{ $condition->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <x-heroicon-o-chevron-down class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />
                        </div>
                        @error('condition')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-slate-700">
                        Deskripsi Kondisi lapangan <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        placeholder="Jelaskan kondisi pasar atau kendala distribusi yang ditemukan di pasar..."
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                    >{{ old('description', $report?->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-10 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-6">
                    <a
                        href="{{ $cancelAction }}"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="cursor-pointer rounded-lg bg-[#2D507E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#2D507E]/90"
                    >
                        {{ $isEdit ? 'Perbarui Laporan' : 'Simpan Laporan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <aside class="min-w-0 self-start lg:col-span-1">
        <div class="rounded-xl border border-slate-200 bg-[#E8F0FB] p-5">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-white text-[#2D507E]">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6" />
                </span>
                <h3 class="text-base font-semibold text-[#1F3A5F]">Catatan laporan</h3>
            </div>

            <p class="mt-4 text-sm leading-snug text-[#2D507E]">
                Gunakan data yang sesuai dengan kondisi pasar. Laporan yang dikirim akan menjadi bahan evaluasi
                dan dapat dilihat oleh Admin serta Kepala Dinas.
            </p>
        </div>
    </aside>
</div>
