@extends('layouts.distributor')

@section('title', 'Manajemen Data Toko')

@section('content')
    <x-ui.page-heading title="Manajemen data toko" subtitle="Kelola daftar toko penerima Minyakita milik distributor">
        <a
            href="{{ route('distributor.stores.create') }}"
            class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-[#2D507E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#2D507E]/90"
        >
            <x-heroicon-o-plus class="h-4 w-4" />
            Tambah data
        </a>
    </x-ui.page-heading>

    <x-ui.flash-messages />

    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="px-5 pt-5">
            <h2 class="text-lg font-semibold text-slate-900">Data toko penerima</h2>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[56rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="bg-[#E8F0FB] text-center text-[11px] uppercase tracking-widest text-[#2D507E]">
                    <tr>
                        <th scope="col" class="w-[24%] px-5 py-3 align-middle font-semibold">Nama Toko</th>
                        <th scope="col" class="w-[30%] px-5 py-3 align-middle font-semibold">Alamat</th>
                        <th scope="col" class="w-[18%] px-5 py-3 align-middle font-semibold">NIB</th>
                        <th scope="col" class="w-[18%] px-5 py-3 align-middle font-semibold">Pasar</th>
                        <th scope="col" class="w-[10%] px-5 py-3 align-middle font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-center">
                    @forelse ($stores as $store)
                        <tr>
                            <td class="px-5 py-3 text-slate-900">
                                {{ $store->name }}
                            </td>
                            <td class="px-5 py-3 text-slate-700">
                                {{ $store->alamat ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-slate-600">
                                {{ $store->nib ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-slate-700">
                                {{ $store->market->name }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <a
                                    href="{{ route('distributor.stores.edit', $store) }}"
                                    aria-label="Edit"
                                    title="Edit"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:border-[#5191E4] hover:text-[#2D507E]"
                                >
                                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">
                                Belum ada data toko penerima
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
