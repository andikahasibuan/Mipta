@extends('layouts.admin')

@section('title', 'Pengguna & Data Master')

@section('page-title', 'Pengguna & Data Master')

@section('content')
    <x-admin.master-tabs active="users">
        <a
            href="{{ route('admin.users.create') }}"
            class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-[#2D507E] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#2D507E]/90"
        >
            <x-heroicon-o-plus class="h-4 w-4" />
            Tambah data
        </a>
    </x-admin.master-tabs>

    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

    @if (session('status'))
        <div role="status" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->has('status'))
        <div role="alert" class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $errors->first('status') }}
        </div>
    @endif

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
            <table class="w-full min-w-[54rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="text-left text-[11px] uppercase tracking-widest text-slate-500">
                    <tr>
                        <th scope="col" class="w-[26%] px-5 py-3 align-middle font-semibold">Nama / Identitas</th>
                        <th scope="col" class="w-[20%] px-5 py-3 align-middle font-semibold">Nama Akun</th>
                        <th scope="col" class="w-[20%] px-5 py-3 align-middle font-semibold">Peran</th>
                        <th scope="col" class="w-[16%] whitespace-nowrap px-5 py-3 align-middle font-semibold">Status Akun</th>
                        <th scope="col" class="w-[18%] whitespace-nowrap px-5 py-3 align-middle font-semibold">Aksi</th>
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
                            <td class="whitespace-nowrap px-5 py-4 align-middle">
                                <div class="flex items-center gap-3">
                                    <a
                                        href="{{ route('admin.users.edit', $user) }}"
                                        class="text-xs font-semibold text-[#2D507E] transition hover:underline"
                                    >
                                        Edit
                                    </a>

                                    @if (auth()->id() !== $user->id)
                                        <form method="POST" action="{{ route('admin.users.status', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                @class([
                                                    'cursor-pointer text-xs font-medium transition hover:underline',
                                                    'text-rose-600 hover:text-rose-700' => $user->is_active,
                                                    'text-emerald-600 hover:text-emerald-700' => ! $user->is_active,
                                                ])
                                            >
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">
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
