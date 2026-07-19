@extends('layouts.site')

@section('title', 'Site-wide blocks — ' . config('app.name'))

{{-- The globals page has no body of its own: its blocks are the header,
     newsletter and footer, rendered around this placeholder by the layout. --}}
@section('content')
    <div class="mx-auto max-w-[1200px] px-5 py-24 text-center">
        <p class="text-xs font-semibold tracking-[0.2em] text-wodi-muted uppercase">Preview</p>
        <h1 class="mt-3 font-heading text-3xl font-extrabold text-wodi-ink sm:text-4xl">
            Site-wide blocks
        </h1>
        <p class="mx-auto mt-4 max-w-md text-[15px] text-wodi-muted">
            The header, newsletter and footer shown here appear on every page.
            Select one to edit it — each page's own content sits in this space.
        </p>
    </div>
@endsection
