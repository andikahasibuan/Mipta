@extends('layouts.admin')

@section('title', 'Data Pengguna')

@section('content')
    @php
        $activeTab = match ($filters['role']) {
            'kepala_dinas' => 'kepala-dinas',
            'uptd_pasar' => 'uptd',
            default => 'admin',
        };
    @endphp

    <x-ui.user-tabs :active="$activeTab">
        <a
            href="{{ route('admin.users.create') }}"
            class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-[#018F98] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#018F98]/90"
        >
            <x-heroicon-o-plus class="h-4 w-4" />
            Tambah data
        </a>
    </x-ui.user-tabs>

    <x-ui.flash-messages />

    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

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
                    placeholder="Cari pengguna..."
                    class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                >
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
            <table class="w-full min-w-[50rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-200 text-center text-[11px] uppercase tracking-widest text-slate-700">
                    <tr>
                        <th scope="col" class="w-[6%] px-3 py-3 align-middle font-semibold">NO</th>
                        <th scope="col" class="w-[24%] px-5 py-3 align-middle font-semibold">Nama / Identitas</th>
                        <th scope="col" class="w-[20%] px-5 py-3 align-middle font-semibold">Nama Akun</th>
                        <th scope="col" class="w-[18%] px-5 py-3 align-middle font-semibold">Peran</th>
                        <th scope="col" class="w-[15%] whitespace-nowrap px-5 py-3 align-middle font-semibold">Status Akun</th>
                        <th scope="col" class="w-[16%] px-5 py-3 align-middle font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-center">
                    @forelse ($users as $user)
                        <tr>
                            <td class="whitespace-nowrap px-3 py-4 align-middle font-mono text-xs text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-5 py-4 align-middle text-slate-900">{{ $user->name }}</td>
                            <td class="px-5 py-4 align-middle">
                                <span class="text-sm text-slate-600">{{ $user->username ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                    {{ $user->role->managementLabel() }}
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
                            <td class="whitespace-nowrap px-5 py-4 align-middle">
                                <x-ui.row-actions
                                    :edit="route('admin.users.edit', $user)"
                                    :delete="route('admin.users.destroy', $user)"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">
                                @if ($filters['q'] !== '' || $filters['status'])
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
