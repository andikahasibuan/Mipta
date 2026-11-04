@extends('layouts.admin')

@section('title', 'Pengguna & Data Master')

@section('page-title', 'Pengguna & Data Master')

@section('content')
    <x-admin.master-tabs active="summary" />

    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

    <div class="flex justify-end">
        <button
            type="button"
            disabled
            aria-disabled="true"
            title="Form tambah pengguna belum tersedia"
            class="inline-flex shrink-0 cursor-not-allowed items-center gap-2 rounded-lg bg-[#2D507E] px-4 py-2.5 text-sm font-semibold text-white opacity-60"
        >
            <x-heroicon-o-plus class="h-5 w-5" />
            Tambah data
        </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="px-5 pt-5">
            <h2 class="text-lg font-semibold text-slate-900">Data pengguna</h2>
        </div>

        <form
            method="GET"
            action="{{ route('admin.users.index') }}"
            class="mt-4 flex flex-col gap-3 px-5 lg:flex-row lg:items-center"
        >
            <div class="relative min-w-0 flex-1">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Cari pengguna & data master..."
                    class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
            </div>

            <select
                name="role"
                onchange="this.form.submit()"
                class="shrink-0 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
            >
                <option value="">Semua peran</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected($filters['role'] === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>

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
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-5 py-4 align-middle text-slate-900">{{ $user->name }}</td>
                            <td class="px-5 py-4 align-middle">
                                <span class="font-mono text-xs text-slate-600">{{ $user->username ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                    {{ $user->role->label() }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-middle">
                                <span @class([
                                    'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium',
                                    'bg-emerald-100 text-emerald-700' => $user->is_active,
                                    'bg-slate-100 text-slate-600' => ! $user->is_active,
                                ])>
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">
                                @if ($filters['q'] !== '' || $filters['role'] || $filters['status'])
                                    Tidak ada pengguna yang sesuai filter
                                @else
                                    Belum ada data pengguna
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->isNotEmpty())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-3">
                <p class="text-xs text-slate-500">
                    Menampilkan {{ $formatNumber($users->count()) }} dari {{ $formatNumber($users->total()) }} pengguna
                </p>

                @if ($users->hasPages())
                    {{ $users->links() }}
                @endif
            </div>
        @endif
    </div>
@endsection
