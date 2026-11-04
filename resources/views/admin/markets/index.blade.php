@extends('layouts.admin')

@section('title', 'Pasar')

@section('page-title', 'Pasar')

@section('content')
    <x-admin.master-tabs active="markets">
        <button
            type="button"
            disabled
            aria-disabled="true"
            title="Fitur tambah data dinonaktifkan"
            class="inline-flex shrink-0 cursor-not-allowed items-center gap-2 rounded-lg bg-[#2D507E] px-4 py-2.5 text-sm font-semibold text-white opacity-60"
        >
            <x-heroicon-o-plus class="h-4 w-4" />
            Tambah data
        </button>
    </x-admin.master-tabs>

    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="px-5 pt-5">
            <h2 class="text-lg font-semibold text-slate-900">Data pasar</h2>
        </div>

        <form
            method="GET"
            action="{{ route('admin.markets.index') }}"
            class="mt-4 flex flex-col gap-3 px-5 lg:flex-row lg:items-center"
        >
            <div class="relative min-w-0 flex-1">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Cari kode atau nama pasar..."
                    class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
            </div>

            <div class="relative shrink-0">
                <select
                    name="status"
                    onchange="this.form.submit()"
                    class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3.5 pr-10 text-sm text-slate-800 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
                    <option value="">Semua Status</option>
                    <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($filters['status'] === 'nonaktif')>Nonaktif</option>
                </select>
                <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>
        </form>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-widest text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Kode</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Nama</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($markets as $market)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-slate-600">
                                {{ $market->code }}
                            </td>
                            <td class="px-5 py-3 text-slate-900">
                                {{ $market->name }}
                            </td>
                            <td class="px-5 py-3">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                    'bg-emerald-100 text-emerald-700' => $market->is_active,
                                    'bg-slate-100 text-slate-600' => ! $market->is_active,
                                ])>
                                    {{ $market->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-500">
                                @if ($filters['q'] !== '' || $filters['status'])
                                    Tidak ada pasar yang sesuai filter
                                @else
                                    Belum ada data pasar
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($markets->isNotEmpty())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-3">
                <p class="text-xs text-slate-500">
                    Menampilkan {{ $formatNumber($markets->count()) }} dari {{ $formatNumber($markets->total()) }} pasar
                </p>

                @if ($markets->hasPages())
                    {{ $markets->links() }}
                @endif
            </div>
        @endif
    </div>
@endsection
