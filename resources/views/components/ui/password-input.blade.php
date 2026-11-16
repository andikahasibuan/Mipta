@props(['id' => 'password', 'name' => 'password'])

<div class="relative">
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="password"
        {{ $attributes->merge(['class' => 'pr-10']) }}
    >

    <button
        type="button"
        data-password-toggle="{{ $id }}"
        aria-label="Tampilkan password"
        title="Tampilkan password"
        class="absolute inset-y-0 right-0 flex w-10 cursor-pointer items-center justify-center text-slate-400 transition hover:text-[#018F98] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#018F98]/40"
    >
        <x-heroicon-o-eye class="h-4 w-4" data-password-icon-show />
        <x-heroicon-o-eye-slash class="hidden h-4 w-4" data-password-icon-hide />
    </button>
</div>
