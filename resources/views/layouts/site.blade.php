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

        {{-- Per-page custom <head> scripts (SEO/analytics), edited on the page
             in the builder. Lives here so the eight page views don't each repeat
             the push. --}}
        @if (! empty($page?->header_scripts))
            {!! $page->header_scripts !!}
        @endif
    </head>
    <body class="min-h-screen overflow-x-hidden bg-wodi-cream font-display text-wodi-ink antialiased">
        @isset($globals['site_navbar'])
            @include('partials.site-header', ['block' => $globals['site_navbar']])
        @endisset

        <main>
            @yield('content')
        </main>

        @isset($globals['newsletter'])
            @include('partials.newsletter', ['block' => $globals['newsletter']])
        @endisset

        @isset($globals['site_footer'])
            @include('partials.site-footer', ['block' => $globals['site_footer']])
        @endisset

        @stack('scripts')

        {{-- Per-page custom end-of-body scripts, edited on the page in the
             builder — the footer counterpart of header_scripts above. --}}
        @if (! empty($page?->footer_scripts))
            {!! $page->footer_scripts !!}
        @endif

        {{-- Editor-only: the preview bridge that lets a page in the builder's
             iframe talk to the editor. Gated on $editor so a visitor never
             loads it. --}}
        @if ($editor ?? false)
            @vite(['resources/css/cms-preview.css', 'resources/js/cms-preview.js'])
        @endif
    </body>
</html>
