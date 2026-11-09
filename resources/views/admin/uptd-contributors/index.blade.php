@extends('layouts.admin')

@section('title', 'Kontributor UPTD')

@section('content')
    <x-ui.master-tabs active="uptd">
        <a
            href="{{ route('admin.uptd-contributors.create') }}"
            class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-[#2D507E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#2D507E]/90"
        >
            <x-heroicon-o-plus class="h-4 w-4" />
            Tambah data
        </a>
    </x-ui.master-tabs>

    <x-ui.flash-messages />

    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="px-5 pt-5">
            <h2 class="text-lg font-semibold text-slate-900">Data kontributor UPTD</h2>
        </div>

        <form
            method="GET"
            action="{{ route('admin.uptd-contributors.index') }}"
            class="mt-4 flex flex-col gap-3 px-5 lg:flex-row lg:items-center"
        >
            <div class="relative min-w-0 flex-1">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Cari nama, username, atau email kontributor..."
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
            <table class="w-full min-w-[44rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="bg-[#E8F0FB] text-center text-[11px] uppercase tracking-widest text-[#2D507E]">
                    <tr>
                        <th scope="col" class="w-[28%] px-5 py-3 align-middle font-semibold">Nama Kontributor</th>
                        <th scope="col" class="w-[22%] px-5 py-3 align-middle font-semibold">Pasar Terkait</th>
                        <th scope="col" class="w-[20%] px-5 py-3 align-middle font-semibold">Peran</th>
                        <th scope="col" class="w-[15%] whitespace-nowrap px-5 py-3 align-middle font-semibold">Status Akun</th>
                        <th scope="col" class="w-[15%] px-5 py-3 align-middle font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-center">
                    @forelse ($contributors as $contributor)
                        <tr>
                            <td class="px-5 py-4 align-middle text-slate-900">{{ $contributor->name }}</td>
                            <td class="px-5 py-4 align-middle">
                                <span class="text-sm text-slate-600">{{ $contributor->market?->name ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                    {{ $contributor->role->label() }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-middle">
                                <span @class([
                                    'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium',
                                    'bg-emerald-100 text-emerald-700' => $contributor->is_active,
                                    'bg-slate-100 text-slate-600' => ! $contributor->is_active,
                                ])>
                                    {{ $contributor->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-middle">
                                <x-ui.row-actions
                                    :edit="route('admin.uptd-contributors.edit', $contributor)"
                                    :delete="route('admin.uptd-contributors.destroy', $contributor)"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">
                                @if ($filters['q'] !== '' || $filters['status'])
                                    Tidak ada kontributor UPTD yang sesuai filter
                                @else
                                    Belum ada data kontributor UPTD
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($contributors->isNotEmpty())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-3">
                <p class="text-xs text-slate-500">
                    Menampilkan {{ $formatNumber($contributors->count()) }} dari {{ $formatNumber($contributors->total()) }} kontributor
                </p>

                @if ($contributors->hasPages())
                    {{ $contributors->links() }}
                @endif
            </div>
        @endif
    </div>
@endsection
