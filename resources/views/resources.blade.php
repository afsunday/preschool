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
            {{-- three colour cards, each with a cutout breaking out the top --}}
            @case('resources_hero')
                @php
                    $src = function ($row, string $key, string $fallback): ?string {
                        $id = data_get($row, $key);

                        return ($id ? \App\Models\Media::find($id)?->url() : null) ?? data_get($row, $fallback);
                    };

                    /*
                     | Each cutout's subject runs to the BOTTOM of its PNG canvas, so anchoring the
                     | image at bottom-0 lands the subject's feet exactly on the card's bottom edge;
                     | the head/arms then break out of the top. Element widths are derived from the
                     | real alpha bboxes (heavy transparent padding, so they're far wider than the
                     | subject's visual width):
                     |   kids-cheer  subject 95.8% of canvas, centre -0.42%  → w 83%
                     |   boy-thumbs  subject 47.3% of canvas, centre -1.52%  → w 191%
                     |   girl-wave   subject 63.7% of canvas, centre +6.88%  → w 142%
                     | translateX = -50% minus the subject's centre offset, so the SUBJECT is centred.
                     |
                     | The geometry belongs to the specific PNG, not to the content — it is keyed
                     | off the cutout and stays here so Tailwind can see the literal classes.
                     */
                    $geometry = fn($v) => match ($v) {
                        'cheer' => ['bg-[#F5344E]', 'aspect-[16/9]', 'w-[83%]', '-translate-x-[49.6%]'],
                        // centre card is taller, per Figma
                        'thumbs' => ['bg-[#22C55E]', 'aspect-square', 'w-[191%]', '-translate-x-[48.5%]'],
                        'wave' => ['bg-wodi-yellow', 'aspect-[16/9]', 'w-[142%]', '-translate-x-[56.9%]'],
                        default => ['bg-wodi-yellow', 'aspect-[16/9]', 'w-[83%]', '-translate-x-1/2'],
                    };
                @endphp

                <section class="relative overflow-hidden bg-wodi-blush">
                    <img src="/images/patterns/grid.png" alt=""
                         class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-60">

                    <div class="relative mx-auto max-w-[1300px] px-4 pt-24 pb-16 lg:px-8 lg:pt-28">
                        <h1 class="font-heading mx-auto max-w-2xl text-center text-3xl leading-tight font-extrabold text-wodi-pink sm:text-4xl lg:text-[42px]">
                            {{ $block->get('title') }}
                        </h1>

                        <p class="mx-auto mt-5 max-w-md text-center text-sm leading-relaxed text-wodi-pink lg:text-[15px]">
                            {{ $block->get('subtitle') }}
                        </p>

                        {{-- three cards — bottom-aligned, centre one taller --}}
                        <div class="mt-36 grid grid-cols-1 items-end gap-x-10 gap-y-36 sm:grid-cols-3 lg:mt-44 lg:gap-x-14">
                            @foreach ($block->get('cards', []) as $card)
                                @php [$bg, $ratio, $w, $shiftX] = $geometry(data_get($card, 'variant')); @endphp

                                <div class="relative">
                                    <div class="{{ $bg }} {{ $ratio }} w-full rounded-2xl"></div>

                                    <img src="{{ $src($card, 'image', 'src') }}" alt="{{ data_get($card, 'alt') }}"
                                         class="pointer-events-none absolute bottom-0 left-1/2 {{ $w }} {{ $shiftX }} max-w-none object-contain">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @break

            {{-- search + filters + cards --}}
            @case('resources_programs')
                @php
                    $src = function ($row, string $key, string $fallback): ?string {
                        $id = data_get($row, $key);

                        return ($id ? \App\Models\Media::find($id)?->url() : null) ?? data_get($row, $fallback);
                    };

                    $label = fn($k) => match ($k) {
                        'download' => 'Download File',
                        'video' => 'Watch Video',
                        'read' => 'Read',
                        default => 'Read',
                    };

                    $icon = fn($k) => match ($k) {
                        'download' => 'lucide-download',
                        'video' => 'lucide-play',
                        default => null,
                    };
                @endphp

                <section id="programs" x-data="{ filter: 'All' }" class="bg-wodi-blush pb-20">
                    <div class="mx-auto max-w-[1400px] px-4 lg:px-8">
                        <h2 class="text-center text-3xl font-bold lg:text-[38px]">{{ $block->get('heading') }}</h2>

                        <p class="mx-auto mt-3 max-w-lg text-center text-sm leading-relaxed text-wodi-muted">
                            {{ $block->get('subheading') }}
                        </p>

                        {{-- search --}}
                        <form action="#" method="GET" class="mx-auto mt-8 max-w-2xl">
                            <div class="flex items-center gap-2 rounded-full bg-white p-1.5 pl-6 shadow-sm">
                                <label for="resource-search" class="sr-only">Search resources</label>

                                <input id="resource-search" name="q" type="search"
                                       placeholder="{{ $block->get('search_placeholder') }}"
                                       class="min-w-0 flex-1 bg-transparent py-2.5 text-sm text-wodi-ink placeholder:text-wodi-muted/70 focus:outline-none">

                                <button type="submit"
                                        class="shrink-0 rounded-full bg-wodi-pink px-8 py-2.5 text-xs font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                                    {{ $block->get('search_label', 'Search') }}
                                </button>
                            </div>
                        </form>

                        {{-- filter tabs — labels are editable, so they go through Js::from
                           | rather than being interpolated raw into the Alpine expression. --}}
                        <div class="no-scrollbar mx-auto mt-6 flex max-w-4xl justify-start gap-2 overflow-x-auto pb-1 sm:justify-center">
                            @foreach ($block->get('filters', []) as $filter)
                                @php $f = data_get($filter, 'label'); @endphp

                                <button type="button"
                                        @click="filter = {{ Js::from($f) }}"
                                        :class="filter === {{ Js::from($f) }}
                                            ? 'bg-wodi-pink text-white'
                                            : 'bg-transparent text-wodi-ink hover:bg-white'"
                                        class="shrink-0 rounded-full px-4 py-1.5 text-[11px] font-medium whitespace-nowrap transition-colors">
                                    {{ $f }}
                                </button>
                            @endforeach
                        </div>

                        {{-- cards --}}
                        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($block->get('cards', []) as $card)
                                @php $action = data_get($card, 'action'); @endphp

                                <article class="flex flex-col rounded-2xl bg-white p-3 shadow-sm">
                                    <img src="{{ $src($card, 'image', 'src') }}" alt=""
                                         loading="lazy"
                                         class="aspect-[4/3] w-full rounded-xl object-cover">

                                    <p class="flex-1 px-2 py-4 text-center text-xs leading-relaxed text-wodi-ink">
                                        {{ data_get($card, 'text') }}
                                    </p>

                                    <a href="{{ data_get($card, 'url', '#') }}"
                                       class="mx-auto mb-1 inline-flex items-center gap-1.5 rounded-full border border-wodi-pink px-6 py-2 text-[11px] font-medium text-wodi-pink transition-colors hover:bg-wodi-pink hover:text-white">
                                        {{ $label($action) }}

                                        @if ($icon($action))
                                            <x-dynamic-component :component="$icon($action)" class="size-3.5" />
                                        @endif
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
