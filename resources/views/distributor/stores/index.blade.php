@extends('layouts.distributor')

@section('title', 'Data Toko')

@section('content')
    <x-ui.flash-messages />

    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-5">
            <h2 class="text-lg font-semibold text-slate-900">Data toko penerima</h2>
            <a
                href="{{ route('distributor.stores.create') }}"
                class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-[#018F98] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#018F98]/90"
            >
                <x-heroicon-o-plus class="h-4 w-4" />
                Tambah data
            </a>
        </div>

        <form
            method="GET"
            action="{{ route('distributor.stores.index') }}"
            class="mt-4 flex flex-col gap-3 px-5 lg:flex-row lg:items-center"
        >
            <div class="relative min-w-0 flex-1">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Cari nama, alamat, atau NIB toko..."
                    class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                >
            </div>

            <div class="relative shrink-0">
                <label for="filter-market" class="sr-only">Pasar</label>
                <select
                    id="filter-market"
                    name="market"
                    onchange="this.form.submit()"
                    class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3.5 pr-11 text-sm text-slate-800 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                >
                    <option value="">Semua Pasar</option>
                    @foreach ($markets as $market)
                        <option value="{{ $market->id }}" @selected($filters['market'] === $market->id)>
                            {{ $market->name }}
                        </option>
                    @endforeach
                </select>
                <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>
        </form>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[52rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-200 text-center text-[11px] uppercase tracking-widest text-slate-700">
                    <tr>
                        <th scope="col" class="w-[6%] px-3 py-3 align-middle font-semibold">NO</th>
                        <th scope="col" class="w-[24%] px-5 py-3 align-middle font-semibold">Nama Toko</th>
                        <th scope="col" class="w-[36%] px-5 py-3 align-middle font-semibold">Alamat</th>
                        <th scope="col" class="w-[20%] px-5 py-3 align-middle font-semibold">NIB</th>
                        <th scope="col" class="w-[14%] px-5 py-3 align-middle font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-center">
                    @forelse ($stores as $store)
                        <tr>
                            <td class="whitespace-nowrap px-3 py-3 font-mono text-xs text-slate-500">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-5 py-3 capitalize text-left text-slate-900">
                                {{ $store->name }}
                            </td>
                            <td class="px-5 py-3 capitalize text-left text-slate-700">
                                {{ $store->alamat_lengkap ?: '—' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-slate-600">
                                {{ $store->nib ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <a
                                    href="{{ route('distributor.stores.edit', $store) }}"
                                    aria-label="Edit"
                                    title="Edit"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-[#018F98] transition hover:border-[#018F98] hover:bg-[#E0F2F3]"
                                >
                                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">
                                @if ($filters['q'] !== '' || $filters['market'] !== null)
                                    Tidak ada toko yang sesuai filter
                                @else
                                    Belum ada data toko penerima
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($stores->isNotEmpty())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-3">
                <p class="text-xs text-slate-500">
                    Menampilkan {{ $formatNumber($stores->count()) }} dari {{ $formatNumber($stores->total()) }} toko
                </p>

                @if ($stores->hasPages())
                    {{ $stores->links() }}
                @endif
            </div>
        @endif
    </div>
@endsection
