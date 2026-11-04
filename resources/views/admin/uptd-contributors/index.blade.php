@extends('layouts.admin')

@section('title', 'Kontributor UPTD')

@section('page-title', 'Kontributor UPTD')

@section('content')
    <x-admin.master-tabs active="uptd" />

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

            <select
                name="status"
                onchange="this.form.submit()"
                class="shrink-0 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
            >
                <option value="">Semua Status</option>
                <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected($filters['status'] === 'nonaktif')>Nonaktif</option>
            </select>
        </form>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[44rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="text-left text-[11px] uppercase tracking-widest text-slate-500">
                    <tr>
                        <th scope="col" class="w-[30%] px-5 py-3 align-middle font-semibold">Nama / Identitas</th>
                        <th scope="col" class="w-[25%] px-5 py-3 align-middle font-semibold">Nama Akun</th>
                        <th scope="col" class="w-[25%] px-5 py-3 align-middle font-semibold">Peran</th>
                        <th scope="col" class="w-[20%] whitespace-nowrap px-5 py-3 align-middle font-semibold">Status Akun</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($contributors as $contributor)
                        <tr>
                            <td class="px-5 py-4 align-middle text-slate-900">{{ $contributor->name }}</td>
                            <td class="px-5 py-4 align-middle">
                                <span class="font-mono text-xs text-slate-600">{{ $contributor->username ?? '—' }}</span>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">
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
