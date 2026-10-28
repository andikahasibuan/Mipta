<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'Masuk')</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white text-slate-800 antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            <div class="hidden bg-white lg:block" aria-hidden="true"></div>

            <div class="flex items-center justify-center bg-blue-100 px-6 py-12">
                <div class="w-full max-w-md rounded-xl bg-white p-8 shadow-sm">
                    @yield('content')
                </div>
            </div>
        </div>
    </body>
</html>
