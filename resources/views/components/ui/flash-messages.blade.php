@if (session('success'))
    <div
        data-flash-message
        role="status"
        class="mt-4 mb-4 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
    >
        <span class="min-w-0 flex-1">{{ session('success') }}</span>

        <button
            type="button"
            data-flash-dismiss
            aria-label="Tutup pesan"
            title="Tutup pesan"
            class="-mr-1 -mt-0.5 inline-flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center rounded-md text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
        >
            <x-heroicon-o-x-mark class="h-4 w-4" />
        </button>
    </div>
@endif

@if ($errors->has('delete'))
    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ $errors->first('delete') }}
    </div>
@endif
