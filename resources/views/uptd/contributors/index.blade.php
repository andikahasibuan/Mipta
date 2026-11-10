@extends('layouts.portal')

@section('title', 'Pilih Identitas Kontributor')

@section('content')
    <header class="w-full border-b border-slate-200 bg-white">
        <div class="flex w-full flex-wrap items-center gap-3 px-4 py-4 sm:gap-4 sm:px-6">
            <img
                src="{{ asset('images/mipta-logo.png') }}"
                alt="MIPTA"
                class="h-16 w-16 shrink-0 object-contain sm:h-20 sm:w-20"
            >
            <div class="min-w-0 leading-tight">
                <p class="text-xl font-semibold tracking-wide text-[#2D507E] sm:text-2xl">MIPTA</p>
                <p class="text-xs text-slate-500 sm:text-sm">Portal UPTD Pasar</p>
            </div>

            <div class="ml-auto">
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
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_2px_12px_rgba(15,23,42,0.06)]">
            <div class="border-b border-slate-100 px-5 py-5 sm:px-6 sm:py-6">
                <h1 class="text-2xl font-semibold text-slate-900">Pilih identitas kontributor</h1>
                <p class="mt-2 text-sm text-slate-500">Identitas menentukan pasar yang dapat dilihat dan dilaporkan pada sesi ini</p>
            </div>

            @if ($contributors->isEmpty())
                <div class="px-5 py-10 text-center text-sm text-slate-500 sm:px-6">
                    Belum ada akun kontributor UPTD yang aktif dan terkait ke pasar.
                </div>
            @else
                @php
                    $currentContributorId = auth()->user()->isMarketUptdContributor() ? auth()->id() : null;
                    $selectedId = old('contributor_id', $currentContributorId);
                    $isCurrentSelection = $currentContributorId !== null && (int) $selectedId === $currentContributorId;
                @endphp

                <form
                    id="contributor-selection-form"
                    method="POST"
                    action="{{ route('uptd.contributors.select') }}"
                    data-current-contributor-id="{{ $currentContributorId }}"
                >
                    @csrf

                    <div class="bg-slate-50 px-5 py-6 sm:px-6 sm:py-7">
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($contributors as $contributor)
                                @php
                                    $number = \Illuminate\Support\Str::afterLast($contributor->username, '.');
                                    $number = ctype_digit($number)
                                        ? str_pad($number, 2, '0', STR_PAD_LEFT)
                                        : str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT);
                                @endphp

                                <label class="block h-full cursor-pointer">
                                    <input
                                        type="radio"
                                        name="contributor_id"
                                        value="{{ $contributor->id }}"
                                        data-name="{{ $contributor->name }}"
                                        class="peer sr-only"
                                        @checked($selectedId == $contributor->id)
                                    >
                                    <span class="flex h-full items-center gap-4 rounded-lg border border-slate-200 bg-white px-5 py-5 shadow-[0_1px_2px_rgba(15,23,42,0.03)] transition hover:border-slate-300 peer-checked:border-[#2D507E] peer-checked:bg-blue-50 peer-checked:ring-1 peer-checked:ring-[#2D507E] peer-focus-visible:ring-2 peer-focus-visible:ring-[#5191E4]">
                                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#E8F0FB] text-sm font-semibold text-[#2D507E]">
                                            {{ $number }}
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-semibold text-slate-900">{{ $contributor->name }}</span>
                                            <span class="mt-1 block truncate text-xs text-slate-500">{{ $contributor->market?->name }}</span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @error('contributor_id')
                            <p class="mt-4 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-4 border-t border-slate-200 bg-white px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-6">
                        <div class="min-w-0">
                            <p class="text-sm text-slate-500">Identitas dipilih</p>
                            <p id="selected-contributor" class="mt-1 truncate text-sm font-medium text-slate-900">Belum ada identitas dipilih</p>
                        </div>

                        <button
                            type="submit"
                            class="inline-flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-md bg-[#1E3A66] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#2D507E] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/50"
                        >
                            <span id="selection-submit-label">{{ $isCurrentSelection ? 'Kembali ke dashboard' : 'Lanjut ke login' }}</span>
                            <x-heroicon-o-chevron-right class="h-4 w-4" />
                        </button>
                    </div>
                </form>
            @endif
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.getElementById('contributor-selection-form');

            if (!form) {
                return;
            }

            const summary = document.getElementById('selected-contributor');
            const submitLabel = document.getElementById('selection-submit-label');
            const currentContributorId = form.dataset.currentContributorId || null;
            const options = form.querySelectorAll('input[name="contributor_id"]');

            const updateSummary = () => {
                const selected = form.querySelector('input[name="contributor_id"]:checked');

                if (!selected) {
                    summary.textContent = 'Belum ada identitas dipilih';
                    submitLabel.textContent = 'Lanjut ke login';

                    return;
                }

                summary.textContent = selected.dataset.name;
                submitLabel.textContent = currentContributorId !== null && selected.value === currentContributorId
                    ? 'Kembali ke dashboard'
                    : 'Lanjut ke login';
            };

            options.forEach((option) => option.addEventListener('change', updateSummary));

            updateSummary();
        })();
    </script>
@endpush
