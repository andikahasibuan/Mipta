@props(['edit', 'delete', 'confirm' => 'Yakin ingin menghapus data ini?'])

<div class="flex items-center justify-center gap-2">
    <a
        href="{{ $edit }}"
        aria-label="Edit"
        title="Edit"
        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-[#018F98] transition hover:border-[#018F98] hover:bg-[#E0F2F3]"
    >
        <x-heroicon-o-pencil-square class="h-4 w-4" />
    </a>

    <form method="POST" action="{{ $delete }}" onsubmit="return confirm('{{ $confirm }}')">
        @csrf
        @method('DELETE')
        <button
            type="submit"
            aria-label="Hapus"
            title="Hapus"
            class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white text-red-500 transition hover:border-red-300 hover:bg-red-50"
        >
            <x-heroicon-o-trash class="h-4 w-4" />
        </button>
    </form>
</div>
