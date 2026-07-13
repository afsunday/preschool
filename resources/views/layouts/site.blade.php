<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name'))</title>
        <meta name="description" content="@yield('meta_description', '')">

        <link rel="icon" href="/images/brand/logo.png" type="image/png">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts
        @vite(['resources/css/site.css', 'resources/js/site.js'])

        @stack('head')
    </head>
    <body class="min-h-screen overflow-x-hidden bg-wodi-cream font-display text-wodi-ink antialiased">
        @include('partials.site-header')

        <main>
            @yield('content')
        </main>

        @include('partials.site-footer')

        @stack('scripts')
    </body>
</html>
