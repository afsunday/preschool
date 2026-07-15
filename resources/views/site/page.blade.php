@extends('layouts.site')

@section('title', $page->meta_title ?: $page->title . ' — ' . config('app.name'))
@section('meta_description', $page->meta_description ?? '')

@if ($page->header_scripts)
    @push('head')
        {!! $page->header_scripts !!}
    @endpush
@endif

@if ($page->footer_scripts)
    @push('scripts')
        {!! $page->footer_scripts !!}
    @endpush
@endif

@section('content')
    {{-- $sections is a SectionCollection: iterate to render in order, or grab
         one by handle, e.g. {!! $sections->section('hero')?->html !!} --}}
    @foreach ($sections as $section)
        {!! $section->html !!}
    @endforeach
@endsection
