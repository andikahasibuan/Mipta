<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'Masuk')</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-dvh bg-white text-slate-800 antialiased">
        <div class="flex md:h-dvh md:overflow-hidden">
            {{-- Lebar gambar mengikuti rasionya (872:900): penuh, tanpa blur, tanpa crop. --}}
            <div class="relative z-10 hidden h-full max-w-[70vw] shrink-0 overflow-hidden bg-[#0B2559] shadow-[6px_0_16px_-6px_rgba(15,23,42,0.35)] md:flex md:justify-center">
                <img
                    src="{{ asset('images/auth/gambar-login.webp') }}"
                    alt="Wali Kota Padang Fadly Amran dan Wakil Wali Kota Padang Maigus Nasir"
                    class="h-full w-auto max-w-none"
                >
            </div>

            <div class="flex min-h-dvh min-w-0 flex-1 items-center justify-center overflow-y-auto bg-white px-6 py-8 md:min-h-0 md:py-6">
                <div class="w-full max-w-md">
                    @yield('content')
                </div>
            </div>
        </div>
    </body>
</html>
