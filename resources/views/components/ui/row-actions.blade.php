@props(['edit', 'delete', 'confirm' => 'Yakin ingin menghapus data ini?'])

<div class="relative">
    <details class="relative" data-row-actions>
        <summary
            aria-label="Aksi"
            title="Aksi"
            class="inline-flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:border-[#5191E4] hover:text-[#2D507E] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#5191E4]/50 [&::-webkit-details-marker]:hidden"
        >
            <x-heroicon-o-eye class="h-5 w-5" />
        </summary>

        <div data-row-actions-menu class="fixed z-50 w-40 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
            <a
                href="{{ $edit }}"
                class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
            >
                <x-heroicon-o-pencil-square class="h-4 w-4 shrink-0" />
                Edit
            </a>

            <form method="POST" action="{{ $delete }}" onsubmit="return confirm('{{ $confirm }}')">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="flex w-full cursor-pointer items-center gap-2.5 border-t border-slate-100 px-4 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50"
                >
                    <x-heroicon-o-trash class="h-4 w-4 shrink-0" />
                    Hapus
                </button>
            </form>
        </div>
    </details>
</div>
