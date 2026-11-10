@php
    $activeMenu = collect($menus)->first(fn (array $menu) => request()->routeIs(...($menu['active'] ?? [$menu['route']])));
    $activeMenuLabel = data_get($activeMenu, 'label', 'Dashboard Monitoring');
@endphp

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
    <body class="h-screen overflow-hidden bg-slate-50 text-slate-800 antialiased">
        <div class="flex h-screen overflow-hidden">
            @include('layouts.partials.sidebar', ['menus' => $menus])

            <main class="flex min-h-0 min-w-0 flex-1 flex-col">
                @include('layouts.partials.header', ['activeMenuLabel' => $activeMenuLabel])

                <div class="flex-1 overflow-y-auto no-scrollbar bg-slate-50 p-6 lg:p-8">
                    @yield('content')
                </div>
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
