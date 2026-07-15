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
            @case('home_hero')
                @php
                    $left = $block->mediaUrl('left_image') ?? '/images/home/hero-girl.png';
                    $right = $block->mediaUrl('right_image') ?? '/images/home/hero-boy.png';
                    $stats = $block->get('stats', []);
                @endphp

                <section x-data="{ y: 0, reduce: window.matchMedia('(prefers-reduced-motion: reduce)').matches, shift(f) { return this.reduce ? 0 : this.y * f; } }" @scroll.window.passive="y = window.scrollY" class="relative overflow-hidden">

                    <img src="/images/patterns/grid.png" alt="" :style="`transform: translate3d(0, ${shift(0.25)}px, 0)`"
                        class="pointer-events-none absolute inset-x-0 top-0 h-[120%] w-full object-cover opacity-60 will-change-transform">

                    @foreach ([['pen', 'top-24 left-6 w-8', 0.32], ['planet-outline', 'top-36 left-[16%] w-9', 0.18], ['swirl', 'top-32 right-[24%] w-7', 0.4], ['planet-ringed', 'top-24 right-8 w-10', 0.22], ['planet-blue', 'right-6 bottom-24 w-10', 0.3], ['earth', 'right-[18%] bottom-16 w-9', 0.16], ['spaceship', 'bottom-20 left-8 w-10', 0.36]] as [$doodle, $pos, $speed])
                        <img src="/images/doodles/{{ $doodle }}.png" alt=""
                            :style="`transform: translate3d(0, ${shift({{ $speed }})}px, 0)`"
                            class="pointer-events-none absolute {{ $pos }} hidden will-change-transform lg:block">
                    @endforeach

                    <div class="relative mx-auto max-w-[1400px] px-5 pt-28 pb-16 lg:px-8 lg:pt-32 lg:pb-24">
                        <div class="grid items-center gap-10 lg:grid-cols-[1fr_minmax(0,640px)_1fr]">
                            <div class="order-2 hidden justify-center lg:order-1 lg:flex"
                                :style="`transform: translate3d(0, ${shift(-0.06)}px, 0)`">
                                <img src="{{ $left }}" alt=""
                                    class="w-full max-w-[300px] object-contain will-change-transform">
                            </div>

                            <div class="order-1 text-center lg:order-2" :style="`transform: translate3d(0, ${shift(0.06)}px, 0)`">
                                <h1
                                    class="font-heading text-4xl leading-tight font-extrabold text-wodi-pink sm:text-5xl lg:text-[52px]">
                                    {{ $block->get('title') }}
                                </h1>

                                @if ($block->get('subtitle'))
                                    <p class="mt-4 text-[15px] text-wodi-muted">{{ $block->get('subtitle') }}</p>
                                @endif

                                <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                                    @if ($block->get('primary_label'))
                                        <a href="{{ $block->get('primary_url', '#') }}"
                                            class="rounded-full bg-wodi-pink px-7 py-3.5 text-sm font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                                            {{ $block->get('primary_label') }}
                                        </a>
                                    @endif
                                    @if ($block->get('secondary_label'))
                                        <a href="{{ $block->get('secondary_url', '#') }}"
                                            class="rounded-full border border-wodi-pink px-7 py-3.5 text-sm font-medium text-wodi-pink transition-colors hover:bg-wodi-pink hover:text-white">
                                            {{ $block->get('secondary_label') }}
                                        </a>
                                    @endif
                                </div>

                                @if ($block->get('lead'))
                                    <p class="mx-auto mt-8 max-w-md text-[15px] leading-relaxed font-medium text-wodi-pink">
                                        {{ $block->get('lead') }}
                                    </p>
                                @endif

                                @if (count($stats))
                                    <div class="mx-auto mt-10 flex max-w-lg items-start justify-between gap-6">
                                        @foreach ($stats as $i => $stat)
                                            <div class="flex items-start gap-2 text-left {{ $i > 0 ? 'self-end' : '' }}">
                                                <x-dynamic-component :component="$i === 0 ? 'lucide-graduation-cap' : 'lucide-book-marked'"
                                                    class="mt-1 size-5 shrink-0 text-wodi-pink" />
                                                <div>
                                                    <p class="text-2xl font-extrabold text-wodi-pink">
                                                        {{ data_get($stat, 'value') }}</p>
                                                    <p class="mt-0.5 max-w-[9rem] text-xs leading-snug text-wodi-muted">
                                                        {{ data_get($stat, 'label') }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="order-3 hidden justify-center lg:flex"
                                :style="`transform: translate3d(0, ${shift(-0.1)}px, 0)`">
                                <img src="{{ $right }}" alt=""
                                    class="w-full max-w-[280px] object-contain will-change-transform">
                            </div>
                        </div>

                        <div class="mt-10 flex items-end justify-center gap-4 lg:hidden">
                            <img src="{{ $left }}" alt="" class="w-1/2 max-w-[180px] object-contain">
                            <img src="{{ $right }}" alt="" class="w-1/2 max-w-[160px] object-contain">
                        </div>
                    </div>
                </section>
            @break

            @case('feature_bar')
                @php
                    $features = $block->get('features', []);
                    $icon = fn($k) => match ($k) {
                        'boxes' => 'lucide-boxes',
                        'users' => 'lucide-users',
                        'megaphone' => 'lucide-megaphone',
                        'shield' => 'lucide-shield',
                        'sparkles' => 'lucide-sparkles',
                        'heart' => 'lucide-heart',
                        default => 'lucide-boxes',
                    };
                @endphp

                <section class="relative mx-auto max-w-[1400px] px-5 lg:px-8">
                    <div
                        class="grid gap-8 rounded-[28px] bg-wodi-maroon px-8 py-10 text-white lg:grid-cols-4 lg:items-center lg:gap-4 lg:px-12">
                        <div class="lg:pr-8">
                            <h2 class="text-3xl font-extrabold lg:text-[32px]">{{ $block->get('lead_title') }}</h2>
                            <p class="mt-3 text-sm leading-relaxed text-white/80">{{ $block->get('lead_body') }}</p>
                        </div>

                        @foreach ($features as $i => $feature)
                            {{-- middle feature is highlighted (design) --}}
                            <div @class([
                                'rounded-2xl bg-wodi-rose p-6' => $i === 1,
                                'lg:border-l lg:border-white/15 lg:pl-8' => $i !== 1,
                            ])>
                                <x-dynamic-component :component="$icon(data_get($feature, 'icon'))" class="size-8" />
                                <h3 class="mt-4 text-lg font-bold">{{ data_get($feature, 'title') }}</h3>
                                <p class="mt-2 text-sm leading-relaxed {{ $i === 1 ? 'text-white/90' : 'text-white/80' }}">
                                    {{ data_get($feature, 'body') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @break

            @case('about_intro')
                @php
                    $image = $block->mediaUrl('image') ?? '/images/home/kids.png';
                @endphp

                <section class="mx-auto max-w-[1400px] px-5 py-16 lg:px-8 lg:py-20">
                    @if ($block->get('eyebrow'))
                        <span
                            class="inline-block rounded-full border border-wodi-pink/40 px-4 py-1 text-xs font-medium text-wodi-pink">
                            {{ $block->get('eyebrow') }}
                        </span>
                    @endif

                    <div class="prose mt-6 max-w-5xl text-2xl leading-relaxed font-medium lg:text-[30px] lg:leading-[1.45]">
                        {!! $block->get('body') !!}
                    </div>

                    <div class="mt-8 inline-flex items-center gap-3 rounded-full bg-white py-2 pr-5 pl-2 shadow-sm">
                        <div class="flex -space-x-2">
                            @for ($i = 1; $i <= 4; $i++)
                                @if (file_exists(public_path("images/home/avatar-{$i}.png")))
                                    <img src="/images/home/avatar-{{ $i }}.png" alt=""
                                        class="size-8 rounded-full border-2 border-white object-cover">
                                @else
                                    <span class="grid size-8 place-items-center rounded-full border-2 border-white bg-wodi-cream">
                                        <x-lucide-user class="size-4 text-wodi-pink/50" />
                                    </span>
                                @endif
                            @endfor
                            <span
                                class="grid size-8 place-items-center rounded-full border-2 border-white bg-wodi-purple text-white">
                                <x-lucide-plus class="size-4" />
                            </span>
                        </div>
                        <p class="text-xs leading-tight font-bold text-wodi-purple">{!! nl2br(e($block->get('trusted_label', "Trusted by\nthousands of users"))) !!}</p>
                    </div>

                    <img src="{{ $image }}" alt=""
                        class="mt-8 h-[220px] w-full rounded-3xl object-cover sm:h-[300px] lg:h-[380px]">
                </section>
            @break

            @case('resource_cards')
                @php
                    $cards = $block->get('cards', []);
                    $accent = fn($k) => match ($k) {
                        'teal' => 'bg-wodi-teal',
                        'yellow' => 'bg-wodi-yellow',
                        'orange' => 'bg-wodi-orange',
                        'purple' => 'bg-wodi-purple',
                        'green' => 'bg-wodi-green',
                        'blue' => 'bg-wodi-blue',
                        'pink' => 'bg-wodi-pink',
                        default => 'bg-wodi-teal',
                    };
                @endphp

                <section class="mx-auto max-w-[1400px] px-5 pb-20 lg:px-8">
                    <div class="flex items-end justify-between gap-6">
                        <div>
                            <h2 class="text-2xl font-bold lg:text-[28px]">{{ $block->get('heading') }}</h2>
                            <p class="mt-1 text-sm text-wodi-muted">{{ $block->get('subheading') }}</p>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <button type="button" aria-label="Previous"
                                class="grid size-9 place-items-center rounded-full bg-wodi-pink text-white hover:bg-wodi-pink-dark">
                                <x-lucide-arrow-left class="size-4" />
                            </button>
                            <button type="button" aria-label="Next"
                                class="grid size-9 place-items-center rounded-full bg-wodi-pink text-white hover:bg-wodi-pink-dark">
                                <x-lucide-arrow-right class="size-4" />
                            </button>
                        </div>
                    </div>

                    <div class="no-scrollbar mt-6 flex snap-x snap-mandatory gap-5 overflow-x-auto pb-2">
                        @foreach ($cards as $card)
                            <article
                                class="{{ $accent(data_get($card, 'accent')) }} flex w-[260px] shrink-0 snap-start flex-col gap-4 rounded-[26px] p-3">
                                @php
                                    $img = data_get($card, 'image');
                                    $url = $img ? \App\Models\Media::find($img)?->url() : null;
                                @endphp
                                <div class="h-[190px] overflow-hidden rounded-[20px] bg-white">
                                    @if ($url)
                                        <img src="{{ $url }}" alt="" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <button type="button"
                                    class="mx-auto inline-flex items-center gap-1.5 rounded-full bg-white px-4 py-1.5 text-[11px] font-medium text-wodi-ink shadow-sm">
                                    {{ data_get($card, 'label', 'download') }}
                                    <x-lucide-download class="size-3.5" />
                                </button>
                            </article>
                        @endforeach
                    </div>
                </section>
            @break

            @case('classroom_split')
                @php
                    $image = $block->mediaUrl('image') ?? '/images/home/kid-with-book.png';
                    $points = $block->get('points', []);
                @endphp

                <section class="relative overflow-hidden bg-white py-16 lg:py-20">
                    <img src="/images/patterns/grid.png" alt=""
                        class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-50">
                    <img src="/images/doodles/heart.png" alt=""
                        class="pointer-events-none absolute top-10 left-6 hidden w-9 lg:block">
                    <img src="/images/doodles/school.png" alt=""
                        class="pointer-events-none absolute bottom-16 left-[8%] hidden w-9 lg:block">
                    <img src="/images/doodles/school-bus.png" alt=""
                        class="pointer-events-none absolute bottom-8 left-[32%] hidden w-10 lg:block">

                    <div class="relative mx-auto grid max-w-[1100px] items-center gap-10 px-5 lg:grid-cols-2 lg:px-8">
                        <div>
                            <h2 class="max-w-md text-3xl leading-snug font-extrabold lg:text-[34px]">{{ $block->get('heading') }}
                            </h2>
                            <ul class="mt-8 space-y-5">
                                @foreach ($points as $point)
                                    <li class="flex items-center gap-3">
                                        <span class="grid size-6 shrink-0 place-items-center rounded-full bg-wodi-pink text-white">
                                            <x-lucide-check class="size-3.5" stroke-width="3" />
                                        </span>
                                        <span class="text-[15px] font-semibold">{{ data_get($point, 'text') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="relative flex justify-center">
                            <span
                                class="absolute top-1/2 left-1/2 aspect-square w-[300px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-wodi-blue sm:w-[340px]"></span>
                            <img src="{{ $image }}" alt=""
                                class="relative w-[260px] object-contain sm:w-[300px]">
                            <span class="absolute top-4 right-6 grid size-9 place-items-center rounded-full bg-white shadow-md">
                                <x-lucide-heart class="size-4 fill-wodi-pink text-wodi-pink" />
                            </span>
                        </div>
                    </div>
                </section>
            @break

            @case('steps')
                @php
                    $steps = $block->get('steps', []);
                    $icon = fn($k) => match ($k) {
                        'user' => 'lucide-user-round',
                        'edit' => 'lucide-square-pen',
                        'id' => 'lucide-id-card',
                        'check' => 'lucide-check',
                        'star' => 'lucide-star',
                        'calendar' => 'lucide-calendar-days',
                        default => 'lucide-user-round',
                    };
                    $count = count($steps);
                @endphp

                <section class="bg-white py-16 lg:py-20">
                    <div class="mx-auto max-w-[1400px] px-5 lg:px-8">
                        <h2 class="text-3xl font-bold lg:text-[38px]">{{ $block->get('heading') }}</h2>
                        <p class="mt-2 text-[15px] text-wodi-muted">{{ $block->get('subheading') }}</p>

                        <div class="mt-14 grid gap-12 lg:grid-cols-3 lg:gap-6">
                            @foreach ($steps as $index => $step)
                                <div class="relative flex flex-col items-center text-center">
                                    @if ($index < $count - 1)
                                        <span
                                            class="absolute top-12 left-[calc(50%+3.5rem)] right-[calc(-50%+3.5rem)] hidden h-px bg-wodi-pink/25 lg:block"></span>
                                    @endif
                                    <div class="relative">
                                        <span class="grid size-24 place-items-center rounded-full bg-wodi-pink/10">
                                            <x-dynamic-component :component="$icon(data_get($step, 'icon'))" class="size-9 text-wodi-pink" />
                                        </span>
                                        <span
                                            class="absolute -top-1 -left-1 grid size-7 place-items-center rounded-full bg-wodi-pink text-xs font-bold text-white">{{ $index + 1 }}</span>
                                    </div>
                                    <h3 class="mt-6 text-lg font-bold">{{ data_get($step, 'title') }}</h3>
                                    <p class="mt-3 max-w-xs text-sm leading-relaxed text-wodi-muted">{{ data_get($step, 'body') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @break

            @case('banner_split')
                @php
                    $image = $block->mediaUrl('image') ?? '/images/home/kids-smile.png';
                @endphp

                <section class="py-16 lg:py-20">
                    <div class="mx-auto max-w-[1200px] px-5 lg:px-8">
                        <div class="grid overflow-hidden rounded-[32px] md:grid-cols-2">
                            <img src="{{ $image }}" alt="" class="h-64 w-full object-cover md:h-full">
                            <div class="flex flex-col justify-center bg-wodi-pink px-8 py-10 text-white lg:px-12">
                                <h2 class="text-2xl leading-snug font-extrabold lg:text-[32px]">{{ $block->get('heading') }}</h2>
                                <p class="mt-5 text-sm leading-relaxed text-white/90">{{ $block->get('body') }}</p>
                            </div>
                        </div>
                    </div>
                </section>
            @break

            @case('testimonials')
                @php
                    $image = $block->mediaUrl('image') ?? '/images/home/testimonial-photo.png';
                    $avatar = $block->mediaUrl('avatar');
                @endphp

                <section class="relative overflow-hidden bg-white py-16 lg:py-20">
                    <img src="/images/patterns/grid.png" alt=""
                        class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-50">

                    <div class="relative mx-auto max-w-[1200px] px-5 lg:px-8">
                        <h2 class="text-center text-3xl font-bold lg:text-[34px]">{!! $block->get('heading') !!}</h2>

                        <div class="mt-12 flex items-center gap-6">
                            <button type="button" aria-label="Previous testimonial"
                                class="hidden size-10 shrink-0 place-items-center rounded-full border border-wodi-pink text-wodi-pink hover:bg-wodi-pink hover:text-white lg:grid">
                                <x-lucide-arrow-left class="size-4" />
                            </button>

                            <figure class="grid flex-1 items-center gap-8 md:grid-cols-2">
                                <img src="{{ $image }}" alt="" class="h-[280px] w-full rounded-3xl object-cover">
                                <div>
                                    <blockquote class="text-lg leading-relaxed text-wodi-ink lg:text-xl">
                                        {{ $block->get('quote') }}</blockquote>
                                    <figcaption class="mt-7 flex items-center gap-3">
                                        @if ($avatar)
                                            <img src="{{ $avatar }}" alt=""
                                                class="size-11 rounded-full object-cover">
                                        @else
                                            <span class="grid size-11 place-items-center rounded-full bg-wodi-cream">
                                                <x-lucide-user class="size-5 text-wodi-pink/50" />
                                            </span>
                                        @endif
                                        <div>
                                            <p class="font-bold">{{ $block->get('name') }}</p>
                                            <p class="text-sm text-wodi-muted">{{ $block->get('role') }}</p>
                                        </div>
                                    </figcaption>
                                </div>
                            </figure>

                            <button type="button" aria-label="Next testimonial"
                                class="hidden size-10 shrink-0 place-items-center rounded-full border border-wodi-pink text-wodi-pink hover:bg-wodi-pink hover:text-white lg:grid">
                                <x-lucide-arrow-right class="size-4" />
                            </button>
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
