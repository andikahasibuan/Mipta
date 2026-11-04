@extends('layouts.admin')

@section('title', 'Distributor')

@section('page-title', 'Distributor')

@section('content')
    <x-admin.master-tabs active="distributors">
        <a
            href="{{ route('admin.distributors.create') }}"
            class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-[#2D507E] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#2D507E]/90"
        >
            <x-heroicon-o-plus class="h-4 w-4" />
            Tambah data
        </a>
    </x-admin.master-tabs>

    @if (session('status'))
        <div role="status" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="px-5 pt-5">
            <h2 class="text-lg font-semibold text-slate-900">Data distributor</h2>
        </div>

        <form
            method="GET"
            action="{{ route('admin.distributors.index') }}"
            class="mt-4 flex flex-col gap-3 px-5 lg:flex-row lg:items-center"
        >
            <div class="relative min-w-0 flex-1">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Cari kode atau nama distributor..."
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
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-widest text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Kode</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Nama</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($distributors as $distributor)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-slate-600">
                                {{ $distributor->code }}
                            </td>
                            <td class="px-5 py-3 text-slate-900">
                                {{ $distributor->name }}
                            </td>
                            <td class="px-5 py-3">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                    'bg-emerald-100 text-emerald-700' => $distributor->is_active,
                                    'bg-slate-100 text-slate-600' => ! $distributor->is_active,
                                ])>
                                    {{ $distributor->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <a
                                    href="{{ route('admin.distributors.edit', $distributor) }}"
                                    class="text-xs font-semibold text-[#2D507E] transition hover:underline"
                                >
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">
                                @if ($filters['q'] !== '' || $filters['status'])
                                    Tidak ada distributor yang sesuai filter
                                @else
                                    Belum ada data distributor
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($distributors->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">
                {{ $distributors->links() }}
            </div>
        @endif
    </div>
@endsection
