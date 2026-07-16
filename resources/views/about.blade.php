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
    @php
        // Editor-picked media wins; otherwise fall back to the shipped asset, but
        // only if it is actually on disk — several of these are still placeholders.
        $onDisk = fn(?string $path) => $path && file_exists(public_path(ltrim($path, '/'))) ? $path : null;
    @endphp

    @foreach ($blocks as $block)
        @if ($editor ?? false)
            <div data-cms-block="{{ $block->id }}">
        @endif

        @switch ($block->type)
            @case('about_hero')
                @php
                    $collage = $block->mediaUrl('image') ?? $block->get('image_src', '/images/about/hero-collage.jpg');
                @endphp

                <section
                    x-data="{
                        y: 0,
                        reduce: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
                        shift(f) { return this.reduce ? 0 : this.y * f },
                    }"
                    @scroll.window.passive="y = window.scrollY"
                    class="relative overflow-hidden bg-wodi-blush">

                    <img src="/images/patterns/grid.png" alt=""
                         :style="`transform: translate3d(0, ${shift(0.22)}px, 0)`"
                         class="pointer-events-none absolute inset-x-0 top-0 h-[120%] w-full object-cover opacity-60 will-change-transform">

                    <img src="/images/doodles/pen.png" alt="" :style="`transform: translate3d(0, ${shift(0.3)}px, 0)`"
                         class="pointer-events-none absolute top-28 left-8 hidden w-8 will-change-transform lg:block">
                    <img src="/images/doodles/planet-outline.png" alt="" :style="`transform: translate3d(0, ${shift(0.18)}px, 0)`"
                         class="pointer-events-none absolute top-40 left-[18%] hidden w-9 will-change-transform lg:block">
                    <img src="/images/doodles/swirl.png" alt="" :style="`transform: translate3d(0, ${shift(0.36)}px, 0)`"
                         class="pointer-events-none absolute top-36 right-[22%] hidden w-7 will-change-transform lg:block">
                    <img src="/images/doodles/planet-ringed.png" alt="" :style="`transform: translate3d(0, ${shift(0.24)}px, 0)`"
                         class="pointer-events-none absolute top-28 right-10 hidden w-10 will-change-transform lg:block">

                    <div class="relative mx-auto max-w-[1400px] px-5 pt-28 pb-10 text-center lg:px-8 lg:pt-32">
                        <h1 class="font-heading mx-auto max-w-2xl text-3xl leading-tight font-extrabold text-wodi-pink sm:text-4xl lg:text-[42px]">
                            {{ $block->get('title') }}
                        </h1>

                        <p class="mx-auto mt-4 max-w-lg text-[15px] text-wodi-muted">
                            {{ $block->get('subtitle') }}
                        </p>
                    </div>

                    {{-- Cloud collage — shared partial (same Figma path as Forms & Policies).
                       | No horizontal padding: it stays flush to the screen edges on small
                       | screens and caps at 1600px on large ones. --}}
                    <div class="relative pb-14">
                        @include('partials.cloud-image', [
                            'src' => $collage,
                            'alt' => $block->get('image_alt', ''),
                            'id'  => 'wodi-cloud-about',
                        ])
                    </div>
                </section>
            @break

            @case('about_founder')
                @php
                    $portrait = $block->mediaUrl('image') ?? $onDisk($block->get('image_src'));
                @endphp

                <section class="mx-auto max-w-3xl px-5 pb-20 text-center lg:px-8">
                    @if ($portrait)
                        <img src="{{ $portrait }}" alt="{{ $block->get('name') }}"
                             class="mx-auto size-16 rounded-full object-cover">
                    @else
                        <span class="mx-auto grid size-16 place-items-center rounded-full bg-neutral-200">
                            <x-lucide-user class="size-7 text-neutral-400" />
                        </span>
                    @endif

                    <p class="mt-5 font-bold">{{ $block->get('name') }}</p>
                    <p class="text-sm font-medium text-wodi-pink">{{ $block->get('role') }}</p>

                    {{-- the quote's emphasis comes from the editor unstyled, so it is
                       | styled from here (Tailwind can't scan the DB). --}}
                    <blockquote class="mt-6 text-[15px] leading-relaxed text-wodi-ink italic lg:text-base [&_span]:font-semibold [&_span]:not-italic">
                        {!! $block->get('quote') !!}
                    </blockquote>
                </section>
            @break

            @case('about_programs')
                @php
                    $src = function ($row) {
                        $id = data_get($row, 'image');

                        return ($id ? \App\Models\Media::find($id)?->url() : null) ?? data_get($row, 'src');
                    };
                @endphp

                <section id="programs" class="bg-wodi-petal py-16 lg:py-20">
                    <div class="mx-auto max-w-[1200px] px-5 lg:px-8">
                        <span class="inline-block rounded-full border border-wodi-pink/40 bg-white px-4 py-1 text-xs font-medium text-wodi-pink">
                            {{ $block->get('eyebrow') }}
                        </span>

                        <div class="mt-6 flex items-end justify-between gap-6">
                            <h2 class="max-w-md text-2xl leading-snug font-bold lg:text-[30px]">
                                {{ $block->get('heading') }}
                            </h2>

                            <div class="flex shrink-0 gap-2">
                                <button type="button" aria-label="Previous"
                                        class="grid size-9 place-items-center rounded-full bg-wodi-pink text-white transition-colors hover:bg-wodi-pink-dark">
                                    <x-lucide-arrow-left class="size-4" />
                                </button>

                                <button type="button" aria-label="Next"
                                        class="grid size-9 place-items-center rounded-full bg-wodi-pink text-white transition-colors hover:bg-wodi-pink-dark">
                                    <x-lucide-arrow-right class="size-4" />
                                </button>
                            </div>
                        </div>

                        <div class="no-scrollbar mt-8 flex snap-x snap-mandatory gap-6 overflow-x-auto pb-2 lg:grid lg:grid-cols-3 lg:overflow-visible">
                            @foreach ($block->get('programs', []) as $program)
                                <article class="w-[280px] shrink-0 snap-start rounded-3xl bg-white p-3 lg:w-auto">
                                    <img src="{{ $src($program) }}" alt="{{ data_get($program, 'alt') }}"
                                         class="h-[190px] w-full rounded-2xl object-cover">

                                    <p class="px-3 py-5 text-center text-sm leading-relaxed text-wodi-ink">
                                        {{ data_get($program, 'body') }}
                                    </p>
                                </article>
                            @endforeach
                        </div>

                        <div class="mt-10 text-center">
                            <a href="{{ $block->get('cta_url', '#') }}"
                               class="inline-block rounded-full bg-wodi-pink px-7 py-3 text-sm font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                                {{ $block->get('cta_label') }}
                            </a>
                        </div>
                    </div>
                </section>
            @break

            @case('about_nurture')
                @php
                    $image = $block->mediaUrl('image') ?? $block->get('image_src', '/images/about/nurturing.jpg');

                    // Each row: a solid colour block on the left, on a light tint of the same hue.
                    $accent = fn($k) => match ($k) {
                        'pink' => ['bg-wodi-pink', 'bg-[#FDE7EF]'],
                        'yellow' => ['bg-wodi-yellow', 'bg-[#FFF6DF]'],
                        'cyan' => ['bg-cyan-400', 'bg-[#E2F8FB]'],
                        'green' => ['bg-wodi-green', 'bg-[#E6F8EC]'],
                        default => ['bg-wodi-pink', 'bg-[#FDE7EF]'],
                    };
                @endphp

                <section class="bg-white py-16 lg:py-20">
                    <div class="mx-auto max-w-[1200px] px-5 text-center lg:px-8">
                        <span class="inline-block rounded-full border border-wodi-pink/40 px-4 py-1 text-xs font-medium text-wodi-pink">
                            {{ $block->get('eyebrow') }}
                        </span>

                        <h2 class="mx-auto mt-5 max-w-sm text-2xl leading-snug font-bold lg:text-[30px]">
                            {{ $block->get('heading') }}
                        </h2>
                    </div>

                    <div class="mx-auto mt-12 grid max-w-[1100px] items-center gap-12 px-5 lg:grid-cols-2 lg:px-8">
                        {{-- Tilted photo: thick wooden/orange frame, rotated, with a sticky note pinned on top --}}
                        <div class="relative mx-auto w-full max-w-md py-6">
                            <div class="-rotate-6 rounded-2xl border-[10px] border-orange-400 bg-orange-400 shadow-2xl">
                                <img src="{{ $image }}" alt="{{ $block->get('image_alt') }}"
                                     class="aspect-[4/3] w-full rounded-xl object-cover">
                            </div>

                            {{-- pinned sticky note --}}
                            <img src="/images/about/pin.png" alt=""
                                 class="pointer-events-none absolute -top-6 left-1/2 w-24 -translate-x-1/2 rotate-3 drop-shadow-md">
                        </div>

                        {{-- colour-coded rows --}}
                        <ul class="space-y-4">
                            @foreach ($block->get('items', []) as $item)
                                @php [$blockColour, $tint] = $accent(data_get($item, 'accent')); @endphp

                                {{-- the whole row tilts on hover --}}
                                <li class="flex items-stretch gap-4 rounded-2xl {{ $tint }} p-3 transition-transform duration-300 ease-out hover:-rotate-2 hover:scale-[1.02]">
                                    <span class="{{ $blockColour }} w-20 shrink-0 self-stretch rounded-xl"></span>

                                    <div class="py-1 pr-2">
                                        <p class="text-sm font-bold">{{ data_get($item, 'title') }}</p>
                                        <p class="mt-1 text-xs leading-relaxed text-wodi-muted">
                                            {{ data_get($item, 'body') }}
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @break

            @case('about_activities')
                @php
                    $src = function ($row) use ($onDisk) {
                        $id = data_get($row, 'image');

                        return ($id ? \App\Models\Media::find($id)?->url() : null) ?? $onDisk(data_get($row, 'src'));
                    };
                @endphp

                <section class="bg-white pb-16 lg:pb-20">
                    <div class="mx-auto max-w-[1200px] px-5 lg:px-8">
                        <span class="inline-block rounded-full border border-wodi-pink/40 px-4 py-1 text-xs font-medium text-wodi-pink">
                            {{ $block->get('eyebrow') }}
                        </span>

                        <h2 class="mt-5 max-w-sm text-2xl leading-snug font-bold lg:text-[30px]">
                            {{ $block->get('heading') }}
                        </h2>

                        <ul class="mt-10 divide-y divide-wodi-ink/10 border-t border-wodi-ink/10">
                            @foreach ($block->get('activities', []) as $activity)
                                @php $img = $src($activity); @endphp

                                <li class="grid items-center gap-6 py-6 md:grid-cols-[auto_1fr_1fr]">
                                    <span class="text-sm font-bold text-wodi-ink md:w-16">{{ data_get($activity, 'number') }}</span>

                                    @if ($img)
                                        <img src="{{ $img }}" alt="{{ data_get($activity, 'title') }}"
                                             class="h-24 w-full max-w-[220px] rounded-xl object-cover">
                                    @else
                                        <span class="h-24 w-full max-w-[220px] rounded-xl bg-neutral-200"></span>
                                    @endif

                                    <div>
                                        <h3 class="font-bold">{{ data_get($activity, 'title') }}</h3>
                                        <p class="mt-1 max-w-md text-xs leading-relaxed text-wodi-muted">
                                            {{ data_get($activity, 'body') }}
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @break

            @case('about_team')
                @php
                    $src = function ($row) use ($onDisk) {
                        $id = data_get($row, 'image');

                        return ($id ? \App\Models\Media::find($id)?->url() : null) ?? $onDisk(data_get($row, 'src'));
                    };

                    $accent = fn($k) => match ($k) {
                        'pink' => ['border-wodi-pink', 'bg-rose-100'],
                        'blue' => ['border-blue-500', 'bg-blue-100'],
                        'amber' => ['border-amber-400', 'bg-amber-100'],
                        'orange' => ['border-wodi-orange', 'bg-orange-100'],
                        default => ['border-wodi-pink', 'bg-rose-100'],
                    };
                @endphp

                <section class="bg-wodi-cream py-16 lg:py-20">
                    <div class="mx-auto max-w-[1200px] px-5 text-center lg:px-8">
                        <span class="inline-block rounded-full border border-wodi-pink/40 bg-white px-4 py-1 text-xs font-medium text-wodi-pink">
                            {{ $block->get('eyebrow') }}
                        </span>

                        <h2 class="mx-auto mt-5 max-w-sm text-2xl leading-snug font-bold lg:text-[30px]">
                            {{ $block->get('heading') }}
                        </h2>

                        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($block->get('members', []) as $member)
                                @php
                                    [$ring, $bg] = $accent(data_get($member, 'accent'));
                                    $avatar = $src($member);
                                @endphp

                                <article class="rounded-3xl border-2 {{ $ring }} bg-white p-3">
                                    @if ($avatar)
                                        <img src="{{ $avatar }}" alt="{{ data_get($member, 'name') }}"
                                             class="h-44 w-full rounded-2xl object-cover">
                                    @else
                                        <span class="grid h-44 w-full place-items-center rounded-2xl {{ $bg }}">
                                            <x-lucide-user-round class="size-12 text-wodi-ink/25" />
                                        </span>
                                    @endif

                                    <h3 class="mt-4 font-bold">{{ data_get($member, 'name') }}</h3>
                                    <p class="mt-0.5 mb-2 text-xs text-wodi-muted">{{ data_get($member, 'role') }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @break

            @case('about_testimonial')
                @php
                    $image = $block->mediaUrl('image') ?? $block->get('image_src', '/images/about/testimonial.jpg');
                @endphp

                <section class="py-16 lg:py-20">
                    <div class="mx-auto max-w-[1000px] px-5 lg:px-8">
                        <div class="relative overflow-hidden rounded-3xl">
                            <img src="{{ $image }}" alt="{{ $block->get('image_alt') }}"
                                 class="h-[420px] w-full object-cover lg:h-[480px]">

                            <figure class="absolute inset-0 grid place-items-center p-6">
                                <div class="relative max-w-sm bg-white px-8 py-10 text-center shadow-xl">
                                    <x-lucide-quote class="absolute top-6 right-6 size-8 fill-wodi-pink text-wodi-pink" />

                                    <blockquote class="text-[15px] leading-relaxed text-wodi-ink italic">
                                        {{ $block->get('quote') }}
                                    </blockquote>

                                    <figcaption class="mt-6 text-sm font-semibold">{{ $block->get('name') }}</figcaption>
                                </div>
                            </figure>
                        </div>
                    </div>
                </section>
            @break

            @case('about_gallery')
                @php
                    $src = function ($row) {
                        $id = data_get($row, 'image');

                        return ($id ? \App\Models\Media::find($id)?->url() : null) ?? data_get($row, 'src');
                    };

                    // Ratios are varied deliberately (the source photos are mostly square).
                    $ratio = fn($k) => match ($k) {
                        '4x5' => 'aspect-[4/5]',
                        '3x4' => 'aspect-[3/4]',
                        '4x3' => 'aspect-[4/3]',
                        '1x1' => 'aspect-square',
                        default => 'aspect-square',
                    };
                @endphp

                <section id="gallery" class="pb-20">
                    <div class="mx-auto max-w-[1100px] px-5 text-center lg:px-8">
                        <span class="inline-block rounded-full border border-wodi-pink/40 px-4 py-1 text-xs font-medium text-wodi-pink">
                            {{ $block->get('eyebrow') }}
                        </span>

                        <h2 class="mx-auto mt-5 max-w-sm text-2xl leading-snug font-bold lg:text-[30px]">
                            {{ $block->get('heading') }}
                        </h2>

                        {{-- Masonry: CSS multi-column + break-inside-avoid so tiles flow and
                           | stagger rather than snapping to a uniform grid. --}}
                        <div class="mt-12 columns-2 gap-4 md:columns-3">
                            @foreach ($block->get('photos', []) as $photo)
                                <img src="{{ $src($photo) }}" alt=""
                                     loading="lazy"
                                     class="{{ $ratio(data_get($photo, 'ratio')) }} mb-4 w-full break-inside-avoid rounded-2xl object-cover">
                            @endforeach
                        </div>
                    </div>
                </section>
            @break

            @case('newsletter')
                @include('partials.newsletter')
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
