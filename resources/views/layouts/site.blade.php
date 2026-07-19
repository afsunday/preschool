<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name'))</title>
        <meta name="description" content="@yield('meta_description', '')">

        {{-- Open Graph / Twitter — the social-share preview. --}}
        @php($ogTitle = $page?->meta_title ?: $page?->title)
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        @if ($ogTitle)
            <meta property="og:title" content="{{ $ogTitle }}">
        @endif
        @if (! empty($page?->meta_description))
            <meta property="og:description" content="{{ $page->meta_description }}">
        @endif
        @if ($page?->ogImageUrl())
            <meta property="og:image" content="{{ $page->ogImageUrl() }}">
            <meta name="twitter:card" content="summary_large_image">
        @endif

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
        {{-- In the globals editor the chrome IS the content, so each block is
             wrapped for selection. Every other preview leaves it plain. --}}
        @php($wrapGlobals = ($editor ?? false) && ($previewGlobals ?? false))

        @isset($globals['site_navbar'])
            @if ($wrapGlobals) <div data-cms-block="{{ $globals['site_navbar']->id }}"> @endif
                @include('partials.site-header', ['block' => $globals['site_navbar']])
            @if ($wrapGlobals) </div> @endif
        @endisset

        <main>
            @yield('content')
        </main>

        @isset($globals['newsletter'])
            @if ($wrapGlobals) <div data-cms-block="{{ $globals['newsletter']->id }}"> @endif
                @include('partials.newsletter', ['block' => $globals['newsletter']])
            @if ($wrapGlobals) </div> @endif
        @endisset

        @isset($globals['site_footer'])
            @if ($wrapGlobals) <div data-cms-block="{{ $globals['site_footer']->id }}"> @endif
                @include('partials.site-footer', ['block' => $globals['site_footer']])
            @if ($wrapGlobals) </div> @endif
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
