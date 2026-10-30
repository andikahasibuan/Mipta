@props(['active' => 'summary'])

<nav class="mb-4 flex gap-1 border-b border-slate-200" aria-label="Navigasi Pengguna dan Data Master">
    <a
        href="{{ route('admin.users.index') }}"
        @if ($active === 'summary') aria-current="page" @endif
        @class([
            '-mb-px border-b-2 px-3 py-2 text-sm transition',
            'border-[#2D507E] font-semibold text-[#2D507E]' => $active === 'summary',
            'border-transparent text-slate-500 hover:text-slate-700' => $active !== 'summary',
        ])
    >
        Ringkasan
    </a>

    <a
        href="{{ route('admin.distributors.index') }}"
        @if ($active === 'distributors') aria-current="page" @endif
        @class([
            '-mb-px border-b-2 px-3 py-2 text-sm transition',
            'border-[#2D507E] font-semibold text-[#2D507E]' => $active === 'distributors',
            'border-transparent text-slate-500 hover:text-slate-700' => $active !== 'distributors',
        ])
    >
        Distributor
    </a>
</nav>
