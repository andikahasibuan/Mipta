@extends('layouts.admin')

@section('title', 'Toko Penerima')

@section('content')
    <x-ui.master-data-tabs active="toko">
        <a
            href="{{ route('admin.recipient-stores.create') }}"
            class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-[#018F98] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#018F98]/90"
        >
            <x-heroicon-o-plus class="h-4 w-4" />
            Tambah data
        </a>
    </x-ui.master-data-tabs>

    <x-ui.flash-messages />

    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="px-5 pt-5">
            <h2 class="text-lg font-semibold text-slate-900">Data toko penerima</h2>
        </div>

        <form
            method="GET"
            action="{{ route('admin.recipient-stores.index') }}"
            class="mt-4 flex flex-col gap-3 px-5 lg:flex-row lg:items-center"
        >
            <div class="relative min-w-0 flex-1">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Cari nama atau alamat toko..."
                    class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                >
            </div>

            <div class="relative shrink-0">
                <select
                    name="market"
                    onchange="this.form.submit()"
                    class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3.5 pr-10 text-sm text-slate-800 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                >
                    <option value="">Semua Pasar</option>
                    @foreach ($markets as $market)
                        <option value="{{ $market->id }}" @selected($filters['market'] === $market->id)>{{ $market->name }}</option>
                    @endforeach
                </select>
                <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>

            <div class="relative shrink-0">
                <select
                    name="distributor"
                    onchange="this.form.submit()"
                    class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3.5 pr-10 text-sm text-slate-800 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                >
                    <option value="">Semua Distributor</option>
                    @foreach ($distributors as $distributor)
                        <option value="{{ $distributor->id }}" @selected($filters['distributor'] === $distributor->id)>{{ $distributor->name }}</option>
                    @endforeach
                </select>
                <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>

            <div class="relative shrink-0">
                <select
                    name="status"
                    onchange="this.form.submit()"
                    class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3.5 pr-10 text-sm text-slate-800 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                >
                    <option value="">Semua Status</option>
                    <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($filters['status'] === 'nonaktif')>Nonaktif</option>
                </select>
                <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>
        </form>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[64rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-200 text-center text-[11px] uppercase tracking-widest text-slate-700">
                    <tr>
                        <th scope="col" class="w-[6%] px-3 py-3 align-middle font-semibold">NO</th>
                        <th scope="col" class="w-[18%] px-5 py-3 align-middle font-semibold">Nama Toko</th>
                        <th scope="col" class="w-[21%] px-5 py-3 align-middle font-semibold">Alamat</th>
                        <th scope="col" class="w-[15%] px-5 py-3 align-middle font-semibold">NIB</th>
                        <th scope="col" class="w-[18%] px-5 py-3 align-middle font-semibold">Distributor</th>
                        <th scope="col" class="w-[10%] px-5 py-3 align-middle font-semibold">Status</th>
                        <th scope="col" class="w-[12%] px-5 py-3 align-middle font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-center">
                    @forelse ($stores as $store)
                        <tr @if (($filters['highlight'] ?? null) === $store->id) data-highlighted-row class="notification-row-highlight" @endif>
                            <td class="whitespace-nowrap px-3 py-3 font-mono text-xs text-slate-600">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-5 py-3 text-left capitalize text-slate-900">
                                {{ $store->name }}
                            </td>
                            <td class="px-5 py-3 text-left capitalize text-slate-700">
                                {{ $store->alamat_lengkap ?: '—' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-slate-600">
                                {{ $store->nib ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-left capitalize text-slate-700">
                                {{ $store->distributor?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                    'bg-emerald-100 text-emerald-700' => $store->is_active,
                                    'bg-slate-100 text-slate-600' => ! $store->is_active,
                                ])>
                                    {{ $store->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <x-ui.row-actions
                                    :edit="route('admin.recipient-stores.edit', $store)"
                                    :delete="route('admin.recipient-stores.destroy', $store)"
                                    :confirm="'Yakin ingin menghapus toko penerima ini?'"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-sm text-slate-500">
                                @if ($filters['q'] !== '' || $filters['market'] || $filters['distributor'] || $filters['status'])
                                    Tidak ada toko penerima yang sesuai filter
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
