@extends('layouts.distributor')

@section('title', 'Manajemen Profil')

@section('content')
    <x-ui.page-heading
        title="Manajemen Profil"
        subtitle="Kelola informasi akun"
    />

    <x-ui.flash-messages />

    @php
        $editableFields = [
            [
                'name' => 'penanggung_jawab',
                'label' => 'Nama penanggung jawab',
                'value' => $distributor->penanggung_jawab,
                'type' => 'text',
            ],
            [
                'name' => 'telepon',
                'label' => 'Nomor Telepon',
                'value' => $distributor->telepon,
                'type' => 'tel',
            ],
            [
                'name' => 'alamat_kantor',
                'label' => 'Alamat kantor',
                'value' => $distributor->alamat_kantor,
                'type' => 'text',
            ],
        ];
    @endphp

    <div class="grid gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] xl:items-start">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-xl font-semibold text-slate-900">Informasi Akun Distributor</h2>
                <p class="mt-1 text-sm text-slate-500">Data identitas yang terhubung dengan akun anda</p>
            </div>

            <div class="px-6 py-6">
                <div class="flex items-center gap-4">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-[#2D507E] text-xl font-semibold text-white">
                        {{ $account->initials() }}
                    </span>

                    <div class="min-w-0">
                        <p class="truncate text-2xl font-semibold text-slate-900">{{ $distributor->name }}</p>
                        <p class="mt-1 text-sm text-slate-500">
                            Distributor MinyaKita
                        </p>
                        <span @class([
                            'mt-2 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium',
                            'bg-emerald-100 text-emerald-700' => $distributor->is_active,
                            'bg-slate-100 text-slate-600' => ! $distributor->is_active,
                        ])>
                            <span @class([
                                'h-1.5 w-1.5 rounded-full',
                                'bg-emerald-500' => $distributor->is_active,
                                'bg-slate-400' => ! $distributor->is_active,
                            ])></span>
                            {{ $distributor->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>

                <dl class="mt-7 grid gap-x-10 gap-y-6 border-t border-slate-200 pt-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-widest text-slate-400">Nama akun</dt>
                        <dd class="mt-1.5 break-words text-sm font-semibold text-slate-900">
                            {{ $account->username }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-widest text-slate-400">Nama distributor</dt>
                        <dd class="mt-1.5 break-words text-sm font-semibold text-slate-900">
                            {{ $distributor->name }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-widest text-slate-400">Email perusahaan</dt>
                        <dd class="mt-1.5 break-words text-sm font-semibold text-slate-900">
                            {{ $account->email }}
                        </dd>
                    </div>

                    @foreach ($editableFields as $field)
                        @php
                            $isEmptyAddress = $field['name'] === 'alamat_kantor' && blank($field['value']);
                        @endphp

                        <div data-inline-field>
                            <dt class="flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-slate-400">
                                {{ $field['label'] }}
                                @if ($isEmptyAddress)
                                    <span class="h-2 w-2 rounded-full bg-amber-500" title="Belum diisi"></span>
                                @endif
                            </dt>
                            <dd class="mt-1.5">
                                <div
                                    data-inline-display
                                    @class([
                                        'flex items-start justify-between gap-3',
                                        'hidden' => $errors->has($field['name']),
                                    ])
                                >
                                    @if ($isEmptyAddress)
                                        <span class="min-w-0 break-words text-sm font-semibold text-amber-600">
                                            Belum diisi
                                        </span>
                                    @else
                                        <span class="min-w-0 break-words text-sm font-semibold text-slate-900">
                                            {{ $field['value'] ?: '—' }}
                                        </span>
                                    @endif
                                    <button
                                        type="button"
                                        data-inline-edit
                                        aria-label="Edit {{ $field['label'] }}"
                                        title="Edit {{ $field['label'] }}"
                                        class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:border-[#5191E4] hover:text-[#2D507E] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#5191E4]/50"
                                    >
                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                    </button>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('distributor.profile.update') }}"
                                    data-inline-form
                                    @class([
                                        'mt-2',
                                        'hidden' => ! $errors->has($field['name']),
                                    ])
                                >
                                    @csrf
                                    @method('PUT')

                                    <input
                                        id="profile-{{ $field['name'] }}"
                                        name="{{ $field['name'] }}"
                                        type="{{ $field['type'] }}"
                                        value="{{ old($field['name'], $field['value']) }}"
                                        @if ($field['name'] === 'penanggung_jawab')
                                            pattern="[A-Za-z .'-]+"
                                            title="Nama penanggung jawab hanya boleh berisi huruf, spasi, titik, apostrof, dan tanda hubung."
                                        @elseif ($field['name'] === 'telepon')
                                            inputmode="numeric"
                                            minlength="10"
                                            maxlength="15"
                                            pattern="[0-9]{10,15}"
                                            title="Nomor telepon hanya boleh berisi angka (10 sampai 15 digit)."
                                        @elseif ($field['name'] === 'alamat_kantor')
                                            pattern="[A-Za-z0-9 .,/'-]+"
                                            title="Alamat kantor hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/'-)."
                                        @endif
                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                                    >

                                    @error($field['name'])
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror

                                    <div class="mt-2 flex items-center gap-2">
                                        <button
                                            type="submit"
                                            class="cursor-pointer rounded-lg bg-[#2D507E] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#2D507E]/90"
                                        >
                                            Simpan
                                        </button>
                                        <button
                                            type="button"
                                            data-inline-cancel
                                            class="cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50"
                                        >
                                            Batal
                                        </button>
                                    </div>
                                </form>
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>

        <div class="space-y-4">
            <section class="rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="text-lg font-semibold text-slate-900">Keamanan akun</h2>

                <div class="mt-5 flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#E8F0FB] text-[#2D507E]">
                        <x-heroicon-o-key class="h-5 w-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-900">Kata sandi</p>
                        <p class="mt-1 text-xs text-slate-500">
                            Terakhir diperbarui {{ $account->updated_at?->diffForHumans() ?? '—' }}
                        </p>
                    </div>
                    <button
                        type="button"
                        data-password-open
                        class="shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-[#2D507E] transition hover:bg-slate-50"
                    >
                        Ubah
                    </button>
                </div>
            </section>
        </div>
    </div>

    <dialog
        id="password-modal"
        class="mx-auto w-full max-w-md rounded-xl border-0 p-0 shadow-xl backdrop:bg-slate-900/50"
    >
        <form method="POST" action="{{ route('distributor.profile.password.update') }}">
            @csrf
            @method('PUT')

            <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Ubah kata sandi</h2>
                    <p class="mt-1 text-xs text-slate-500">Masukkan kata sandi saat ini untuk mengonfirmasi perubahan.</p>
                </div>
                <button
                    type="button"
                    data-password-close
                    aria-label="Tutup"
                    class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                >
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>

            <div class="space-y-4 px-5 py-5">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700">Kata sandi saat ini</label>
                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        autocomplete="current-password"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                    >
                    @error('current_password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Kata sandi baru</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                    >
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi kata sandi baru</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                    >
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-5 py-4">
                <button
                    type="button"
                    data-password-close
                    class="cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="cursor-pointer rounded-lg bg-[#2D507E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#2D507E]/90"
                >
                    Simpan kata sandi
                </button>
            </div>
        </form>
    </dialog>

    @push('scripts')
        <script>
            document.querySelectorAll('[data-inline-field]').forEach((field) => {
                const display = field.querySelector('[data-inline-display]');
                const form = field.querySelector('[data-inline-form]');
                const input = form?.querySelector('input[name]');

                field.querySelector('[data-inline-edit]')?.addEventListener('click', () => {
                    display?.classList.add('hidden');
                    form?.classList.remove('hidden');
                    input?.focus();
                    input?.select();
                });

                field.querySelector('[data-inline-cancel]')?.addEventListener('click', () => {
                    form?.classList.add('hidden');
                    display?.classList.remove('hidden');
                });
            });

            const passwordModal = document.getElementById('password-modal');

            const openPasswordModal = () => {
                if (!passwordModal) {
                    return;
                }

                passwordModal.showModal();
                passwordModal.querySelector('input[name="current_password"]')?.focus();
            };

            document.querySelector('[data-password-open]')?.addEventListener('click', openPasswordModal);

            document.querySelectorAll('[data-password-close]').forEach((button) => {
                button.addEventListener('click', () => passwordModal?.close());
            });

            @if ($errors->hasAny(['current_password', 'password']))
                openPasswordModal();
            @endif
        </script>
    @endpush
@endsection
