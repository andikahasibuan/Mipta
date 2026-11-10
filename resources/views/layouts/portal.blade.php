<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('layouts.partials.favicon')

        <title>@yield('title', 'MIPTA')</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-dvh bg-[#F3F7FB] text-slate-800 antialiased">
        @yield('content')

        @stack('scripts')
    </body>
</html>
