@extends('layouts.site')

@section('title', 'About us — ' . config('app.name'))
@section('meta_description', 'We are not a holding space. We are an active development environment for children aged 0-5.')

@section('content')

    {{-- ============================================================
     | HERO
     ============================================================ --}}
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
                Build credibility and emotional confidence fast.
            </h1>

            <p class="mx-auto mt-4 max-w-lg text-[15px] text-wodi-muted">
                We are not a holding space. We are an active development environment for children aged 0-5.
            </p>
        </div>

        {{-- Cloud collage — shared partial (same Figma path as Forms & Policies).
           | No horizontal padding: it stays flush to the screen edges on small
           | screens and caps at 1600px on large ones. --}}
        <div class="relative pb-14">
            @include('partials.cloud-image', [
                'src' => '/images/about/hero-collage.jpg',
                'alt' => 'Teacher playing with children',
                'id'  => 'wodi-cloud-about',
            ])
        </div>
    </section>

    {{-- ============================================================
     | FOUNDER QUOTE
     ============================================================ --}}
    <section class="mx-auto max-w-3xl px-5 pb-20 text-center lg:px-8">
        @if (file_exists(public_path('images/about/founder.jpg')))
            <img src="/images/about/founder.jpg" alt="Dr. Mery Osayi"
                 class="mx-auto size-16 rounded-full object-cover">
        @else
            <span class="mx-auto grid size-16 place-items-center rounded-full bg-neutral-200">
                <x-lucide-user class="size-7 text-neutral-400" />
            </span>
        @endif

        <p class="mt-5 font-bold">Dr. Mery Osayi, OMN</p>
        <p class="text-sm font-medium text-wodi-pink">Founder &amp; Director, WODI</p>

        <blockquote class="mt-6 text-[15px] leading-relaxed text-wodi-ink italic lg:text-base">
            When I started WODI, I had one vision: to create the kind of early childhood centre I wished had
            existed for my own children. A place that sees every child
            <span class="font-semibold not-italic">fully</span>
            — not just what they can do, but who they are becoming.
            Today, I'm incredibly proud of the team we've built and the families who trust us with their most
            precious people. WODI isn't just a centre. It's a family.
        </blockquote>
    </section>

    {{-- ============================================================
     | PROGRAMS AND CLASSES
     ============================================================ --}}
    @php
        $programs = [
            ['img' => '/images/about/program-art.jpg',       'alt' => "Children's crayon artwork"],
            ['img' => '/images/about/program-drawing.jpg',   'alt' => 'Child drawing on a wall'],
            ['img' => '/images/about/program-classroom.jpg', 'alt' => 'Children working at classroom tables'],
        ];
    @endphp

    <section id="programs" class="bg-wodi-petal py-16 lg:py-20">
        <div class="mx-auto max-w-[1200px] px-5 lg:px-8">
            <span class="inline-block rounded-full border border-wodi-pink/40 bg-white px-4 py-1 text-xs font-medium text-wodi-pink">
                Programs and Classes
            </span>

            <div class="mt-6 flex items-end justify-between gap-6">
                <h2 class="max-w-md text-2xl leading-snug font-bold lg:text-[30px]">
                    Playful programs for growing minds
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
                @foreach ($programs as $program)
                    <article class="w-[280px] shrink-0 snap-start rounded-3xl bg-white p-3 lg:w-auto">
                        <img src="{{ $program['img'] }}" alt="{{ $program['alt'] }}"
                             class="h-[190px] w-full rounded-2xl object-cover">

                        <p class="px-3 py-5 text-center text-sm leading-relaxed text-wodi-ink">
                            Potter ipsum wand elf parchment wingardium. Teacup do feint teacup.
                        </p>
                    </article>
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('home') }}#admissions"
                   class="inline-block rounded-full bg-wodi-pink px-7 py-3 text-sm font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                    Enroll Your Child Today
                </a>
            </div>
        </div>
    </section>

    {{-- ============================================================
     | NURTURING YOUNG HEARTS
     ============================================================ --}}
    @php
        // Each row: a solid colour block on the left, on a light tint of the same hue.
        $nurture = [
            ['block' => 'bg-wodi-pink',  'tint' => 'bg-[#FDE7EF]'],
            ['block' => 'bg-wodi-yellow','tint' => 'bg-[#FFF6DF]'],
            ['block' => 'bg-cyan-400',   'tint' => 'bg-[#E2F8FB]'],
            ['block' => 'bg-wodi-green', 'tint' => 'bg-[#E6F8EC]'],
        ];
    @endphp

    <section class="bg-white py-16 lg:py-20">
        <div class="mx-auto max-w-[1200px] px-5 text-center lg:px-8">
            <span class="inline-block rounded-full border border-wodi-pink/40 px-4 py-1 text-xs font-medium text-wodi-pink">
                Lorem ipsum section
            </span>

            <h2 class="mx-auto mt-5 max-w-sm text-2xl leading-snug font-bold lg:text-[30px]">
                Nurturing young hearts dreams
            </h2>
        </div>

        <div class="mx-auto mt-12 grid max-w-[1100px] items-center gap-12 px-5 lg:grid-cols-2 lg:px-8">
            {{-- Tilted photo: thick wooden/orange frame, rotated, with a sticky note pinned on top --}}
            <div class="relative mx-auto w-full max-w-md py-6">
                <div class="-rotate-6 rounded-2xl border-[10px] border-orange-400 bg-orange-400 shadow-2xl">
                    <img src="/images/about/nurturing.jpg" alt="Children drawing at a desk"
                         class="aspect-[4/3] w-full rounded-xl object-cover">
                </div>

                {{-- pinned sticky note --}}
                <img src="/images/about/pin.png" alt=""
                     class="pointer-events-none absolute -top-6 left-1/2 w-24 -translate-x-1/2 rotate-3 drop-shadow-md">
            </div>

            {{-- colour-coded rows --}}
            <ul class="space-y-4">
                @foreach ($nurture as $item)
                    {{-- the whole row tilts on hover --}}
                    <li class="flex items-stretch gap-4 rounded-2xl {{ $item['tint'] }} p-3 transition-transform duration-300 ease-out hover:-rotate-2 hover:scale-[1.02]">
                        <span class="{{ $item['block'] }} w-20 shrink-0 self-stretch rounded-xl"></span>

                        <div class="py-1 pr-2">
                            <p class="text-sm font-bold">Potter ipsum wand elf parchment</p>
                            <p class="mt-1 text-xs leading-relaxed text-wodi-muted">
                                Potter ipsum wand elf parchment wingardium. Golden for easy you've flying
                                cup die world pear-tickle. Where chance gillyweed armchairs beaded.
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ============================================================
     | FUN ACTIVITIES
     ============================================================ --}}
    @php
        $activities = [
            ['no' => '01', 'title' => 'Art & Craft'],
            ['no' => '02', 'title' => 'Storytelling & Reading'],
            ['no' => '03', 'title' => 'Music & Movement'],
            ['no' => '04', 'title' => 'Big games and puzzle'],
        ];
    @endphp

    <section class="bg-white pb-16 lg:pb-20">
        <div class="mx-auto max-w-[1200px] px-5 lg:px-8">
            <span class="inline-block rounded-full border border-wodi-pink/40 px-4 py-1 text-xs font-medium text-wodi-pink">
                Learning activities
            </span>

            <h2 class="mt-5 max-w-sm text-2xl leading-snug font-bold lg:text-[30px]">
                Fun activities that inspire young minds
            </h2>

            <ul class="mt-10 divide-y divide-wodi-ink/10 border-t border-wodi-ink/10">
                @foreach ($activities as $i => $activity)
                    <li class="grid items-center gap-6 py-6 md:grid-cols-[auto_1fr_1fr]">
                        <span class="text-sm font-bold text-wodi-ink md:w-16">{{ $activity['no'] }}</span>

                        @php $img = "images/about/activity-" . ($i + 1) . ".jpg"; @endphp

                        @if (file_exists(public_path($img)))
                            <img src="/{{ $img }}" alt="{{ $activity['title'] }}"
                                 class="h-24 w-full max-w-[220px] rounded-xl object-cover">
                        @else
                            <span class="h-24 w-full max-w-[220px] rounded-xl bg-neutral-200"></span>
                        @endif

                        <div>
                            <h3 class="font-bold">{{ $activity['title'] }}</h3>
                            <p class="mt-1 max-w-md text-xs leading-relaxed text-wodi-muted">
                                Potter ipsum wand elf parchment wingardium. Golden for easy you've flying cup
                                die world pear-tickle. Where chance gillyweed armchairs beaded.
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ============================================================
     | TEAM
     ============================================================ --}}
    @php
        $team = [
            ['name' => 'Jodi McDermott',       'ring' => 'border-wodi-pink',   'bg' => 'bg-rose-100'],
            ['name' => 'Lillian Satterfield',  'ring' => 'border-blue-500',    'bg' => 'bg-blue-100'],
            ['name' => 'Samuel Becker-Harvey', 'ring' => 'border-amber-400',   'bg' => 'bg-amber-100'],
            ['name' => 'Sean Prosacco',        'ring' => 'border-wodi-orange', 'bg' => 'bg-orange-100'],
        ];
    @endphp

    <section class="bg-wodi-cream py-16 lg:py-20">
        <div class="mx-auto max-w-[1200px] px-5 text-center lg:px-8">
            <span class="inline-block rounded-full border border-wodi-pink/40 bg-white px-4 py-1 text-xs font-medium text-wodi-pink">
                Lorem ipsum section
            </span>

            <h2 class="mx-auto mt-5 max-w-sm text-2xl leading-snug font-bold lg:text-[30px]">
                Caring hearts behind every happy child
            </h2>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($team as $i => $member)
                    @php $avatar = "images/about/teacher-" . ($i + 1) . ".png"; @endphp

                    <article class="rounded-3xl border-2 {{ $member['ring'] }} bg-white p-3">
                        @if (file_exists(public_path($avatar)))
                            <img src="/{{ $avatar }}" alt="{{ $member['name'] }}"
                                 class="h-44 w-full rounded-2xl object-cover">
                        @else
                            <span class="grid h-44 w-full place-items-center rounded-2xl {{ $member['bg'] }}">
                                <x-lucide-user-round class="size-12 text-wodi-ink/25" />
                            </span>
                        @endif

                        <h3 class="mt-4 font-bold">{{ $member['name'] }}</h3>
                        <p class="mt-0.5 mb-2 text-xs text-wodi-muted">Grade Teacher</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
     | TESTIMONIAL
     ============================================================ --}}
    <section class="py-16 lg:py-20">
        <div class="mx-auto max-w-[1000px] px-5 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl">
                <img src="/images/about/testimonial.jpg" alt="Children in a classroom"
                     class="h-[420px] w-full object-cover lg:h-[480px]">

                <figure class="absolute inset-0 grid place-items-center p-6">
                    <div class="relative max-w-sm bg-white px-8 py-10 text-center shadow-xl">
                        <x-lucide-quote class="absolute top-6 right-6 size-8 fill-wodi-pink text-wodi-pink" />

                        <blockquote class="text-[15px] leading-relaxed text-wodi-ink italic">
                            Potter ipsum wand elf parchment wingardium. Golden for easy you've flying cup die
                            world pear-tickle. Where chance gillyweed armchairs beaded.
                        </blockquote>

                        <figcaption class="mt-6 text-sm font-semibold">Sarah Finlay</figcaption>
                    </div>
                </figure>
            </div>
        </div>
    </section>

    {{-- ============================================================
     | GALLERY
     ============================================================ --}}
    <section id="gallery" class="pb-20">
        <div class="mx-auto max-w-[1100px] px-5 text-center lg:px-8">
            <span class="inline-block rounded-full border border-wodi-pink/40 px-4 py-1 text-xs font-medium text-wodi-pink">
                Lorem ipsum section
            </span>

            <h2 class="mx-auto mt-5 max-w-sm text-2xl leading-snug font-bold lg:text-[30px]">
                Caring hearts behind every happy child
            </h2>

            {{-- Masonry: CSS multi-column + break-inside-avoid so tiles flow and
               | stagger rather than snapping to a uniform grid. Ratios are varied
               | deliberately (the source photos are mostly square). --}}
            @php
                $galleryRatios = [
                    'aspect-[4/5]',   // 1 — tall
                    'aspect-[4/3]',   // 2 — wide
                    'aspect-square',  // 3
                    'aspect-[3/4]',   // 4 — tall
                    'aspect-[4/3]',   // 5 — wide
                    'aspect-square',  // 6
                    'aspect-[3/4]',   // 7 — tall
                    'aspect-[4/3]',   // 8 — wide
                ];
            @endphp

            <div class="mt-12 columns-2 gap-4 md:columns-3">
                @foreach ($galleryRatios as $i => $ratio)
                    <img src="/images/about/gallery-{{ $i + 1 }}.jpg" alt=""
                         loading="lazy"
                         class="{{ $ratio }} mb-4 w-full break-inside-avoid rounded-2xl object-cover">
                @endforeach
            </div>
        </div>
    </section>

    @include('partials.newsletter')

@endsection
