@extends('layouts.admin')

@section('title', 'Distributor')

@section('page-title', 'Distributor')

@section('content')
    <x-admin.master-tabs active="distributors" />

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-widest text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Kode</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Nama</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Status</th>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-500">
                                Belum ada data distributor
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
