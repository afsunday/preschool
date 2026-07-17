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
    @foreach ($blocks as $block)
        @if ($editor ?? false)
            <div data-cms-block="{{ $block->id }}">
        @endif

        @switch ($block->type)
            @case('forms_hero')
                @php
                    $collage = $block->mediaUrl('image') ?? $block->get('image_src', '/images/about/hero-collage.jpg');
                @endphp

                <section class="relative overflow-hidden bg-wodi-blush">
                    <img src="/images/patterns/grid.png" alt=""
                         class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-60">

                    <div class="relative mx-auto max-w-[1400px] px-4 pt-24 pb-10 text-center lg:px-8 lg:pt-28">
                        <h1 class="font-heading mx-auto max-w-xl text-3xl leading-tight font-extrabold text-wodi-pink sm:text-4xl lg:text-[42px]">
                            {{ $block->get('title') }}
                        </h1>

                        {{-- links come from the editor unstyled, so the anchor styling is
                           | applied from here (Tailwind can't scan the DB). --}}
                        <p class="mx-auto mt-6 max-w-2xl text-sm leading-relaxed text-wodi-pink lg:text-[15px] [&_a]:underline [&_a]:underline-offset-2 [&_a:hover]:no-underline">
                            {!! $block->get('body') !!}
                        </p>

                        <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-wodi-pink lg:text-[15px]">
                            {{ $block->get('body_secondary') }}
                        </p>
                    </div>

                    {{-- cloud collage (shared partial — same Figma path as the About hero) --}}
                    <div class="relative pb-14">
                        @include('partials.cloud-image', [
                            'src' => $collage,
                            'alt' => $block->get('image_alt', ''),
                            'id'  => 'wodi-cloud-forms',
                        ])
                    </div>
                </section>
            @break

            @case('forms_downloads')
                @php
                    $src = function ($row, string $key, string $fallback): ?string {
                        $id = data_get($row, $key);

                        return ($id ? \App\Models\Media::find($id)?->url() : null) ?? data_get($row, $fallback);
                    };
                @endphp

                <section class="bg-wodi-blush pb-20">
                    <div class="mx-auto max-w-[1400px] px-4 lg:px-8">
                        {{-- mixed-weight lead --}}
                        <p class="max-w-4xl text-2xl leading-snug lg:text-[30px]">
                            <span class="text-wodi-muted">{{ $block->get('lead_start') }}</span>
                            <span class="font-bold text-wodi-ink">{{ $block->get('lead_emphasis') }}</span>
                            <span class="text-wodi-muted">{{ $block->get('lead_end') }}</span>
                        </p>

                        <div class="mx-auto mt-16 grid max-w-[1150px] gap-6 md:grid-cols-3">
                            @foreach ($block->get('forms', []) as $form)
                                <article class="flex flex-col items-center rounded-2xl bg-white p-8 text-center shadow-sm">
                                    <img src="{{ $src($form, 'image', 'src') }}" alt="{{ data_get($form, 'title') }}"
                                         class="size-32 rounded-full object-cover">

                                    <h2 class="mt-6 text-base font-bold text-wodi-ink">{{ data_get($form, 'title') }}</h2>

                                    <div class="mt-3 space-y-1">
                                        @foreach (data_get($form, 'lines', []) as $line)
                                            <p class="text-xs text-wodi-ink underline underline-offset-2">{{ data_get($line, 'text') }}</p>
                                        @endforeach
                                    </div>

                                    <a href="{{ data_get($form, 'cta_url', '#') }}"
                                       class="mt-8 block w-full rounded-full border border-wodi-ink/20 py-3.5 text-center text-xs font-medium text-wodi-muted transition-colors hover:border-wodi-pink hover:text-wodi-pink">
                                        {{ data_get($form, 'cta_label', 'Download Form') }}
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @break
        @endswitch

        @if ($editor ?? false)
            </div>
        @endif
    @endforeach
@endsection

@if ($editor ?? false)
    @push('scripts')
        <style>
            [data-cms-block] {
                outline: 2px solid transparent;
                outline-offset: -2px;
                transition: outline-color .1s;
            }

            [data-cms-block]:hover {
                outline-color: rgba(236, 30, 121, .45);
                cursor: pointer;
            }

            [data-cms-block].cms-selected {
                outline-width: 3px;
                outline-offset: -3px;
                outline-color: #ec1e79;
            }
        </style>
        <script>
            (function() {
                const post = (m) => parent.postMessage({
                    source: 'cms-preview',
                    ...m
                }, '*');
                document.querySelectorAll('[data-cms-block]').forEach((el) => {
                    el.addEventListener('click', (e) => {
                        e.preventDefault();
                        post({
                            type: 'select',
                            id: Number(el.dataset.cmsBlock)
                        });
                    });
                });
                window.addEventListener('message', (e) => {
                    const m = e.data || {};
                    if (m.source !== 'cms-editor') return;
                    if (m.type === 'select') {
                        document.querySelectorAll('.cms-selected').forEach((n) => n.classList.remove(
                            'cms-selected'));
                        const el = document.querySelector('[data-cms-block="' + m.id + '"]');
                        if (el) {
                            el.classList.add('cms-selected');
                            el.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    }
                });
                post({
                    type: 'ready'
                });
            })();
        </script>
    @endpush
@endif
