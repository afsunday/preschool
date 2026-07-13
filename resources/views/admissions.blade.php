@extends('layouts.site')

@section('title', 'Admissions — ' . config('app.name'))
@section('meta_description', "All enrolments are processed through One List, the government's centralised early childhood registration portal.")

@section('content')

    {{-- ============================================================
     | HERO — yellow card
     ============================================================ --}}
    {{-- The girl deliberately BREAKS OUT of the yellow card (legs/books hang below it),
       | so she cannot live inside the clipped card. The card clips only its pattern;
       | she is absolutely positioned over it on desktop, and in-flow on mobile. --}}
    {{-- px-4 on mobile, px-8 (32px) from lg — 32px is what puts the card at x=32 per Figma --}}
    <section class="px-4 pt-[68px] pb-16 lg:px-8 lg:pb-28">
        <div class="relative mx-auto max-w-[1600px]">

            {{-- Yellow card --}}
            <div class="relative overflow-hidden rounded-3xl bg-wodi-yellow">
                <img src="/images/admissions/doodle-pattern.png" alt=""
                     class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-[0.07]">

                {{-- Card is ~2.05:1 in Figma (1196x582), so it needs real height —
                   | at 420px the girl was pushed to ~49% down instead of ~23%. --}}
                <div class="relative z-20 grid gap-8 px-6 py-8 lg:min-h-[582px] lg:grid-cols-2 lg:px-10 lg:py-[60px]">

                    {{-- Left --}}
                    <div class="max-w-[19rem]">
                        <h1 class="font-heading text-3xl leading-tight font-extrabold text-wodi-ink lg:text-[34px]">
                            Ready to get Started? Enrolment is easy.
                        </h1>

                        {{-- dotted connector — arrowheads at BOTH ends, as in Figma --}}
                        <div class="my-7 ml-4 hidden flex-col items-center lg:flex lg:w-fit">
                            <x-lucide-chevron-up class="size-3.5 text-wodi-ink" stroke-width="2.5" />
                            <span class="h-24 border-l-2 border-dashed border-wodi-ink"></span>
                            <x-lucide-chevron-down class="size-3.5 text-wodi-ink" stroke-width="2.5" />
                        </div>

                        <div class="mt-6 lg:mt-0">
                            <p class="text-base font-bold text-wodi-ink">Hello There!</p>
                            <p class="mt-1 max-w-[13rem] text-[13px] leading-relaxed text-wodi-ink/85">
                                Have questions about the process?
                                <a href="#" class="font-semibold underline underline-offset-2">Contact us</a>
                                — we're happy to help you navigate enrolment.
                            </p>
                        </div>
                    </div>

                    {{-- Right — LEFT-aligned copy in a right-hand column (per Figma) --}}
                    <div class="max-w-[17rem] lg:justify-self-end">
                        <p class="text-[13px] leading-snug font-semibold text-wodi-ink">
                            Securing your child's spot at WODI Daycare begins with the government's official
                            placement system — OneList Waterloo Region.
                        </p>

                        <a href="#enrol"
                           class="mt-1 block w-36 rounded-full bg-wodi-pink py-2.5 text-center text-[11px] font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                            Get Started
                        </a>
                    </div>
                </div>

                {{-- Mobile: girl sits in flow at the bottom of the card --}}
                <img src="/images/admissions/hero-girl-books.png" alt="Pupil leaning on a stack of books"
                     class="relative mx-auto -mt-2 w-64 object-contain lg:hidden">

                {{-- 3k+ pill — flush to the card's right edge, ~79% down --}}
                {{-- Rounded on the LEFT only: it runs flush into the card's right edge,
                   | so the right end is square (not a full pill). --}}
                <div class="absolute right-0 bottom-[82px] z-20 hidden items-center gap-6 rounded-l-full bg-wodi-ink py-[18px] pr-10 pl-5 lg:flex">
                    <div class="flex -space-x-3">
                        @for ($i = 1; $i <= 4; $i++)
                            <img src="/images/admissions/class-{{ $i }}.jpg" alt=""
                                 class="size-11 rounded-full border-2 border-white object-cover">
                        @endfor

                        <span class="grid size-11 place-items-center rounded-full border-2 border-white bg-wodi-pink text-white">
                            <x-lucide-plus class="size-5" />
                        </span>
                    </div>

                    <div class="text-white">
                        <p class="text-2xl leading-none font-bold">3k+</p>
                        <p class="mt-0.5 text-xs text-white/80">Users</p>
                    </div>
                </div>
            </div>

            {{-- Desktop: girl overflows the card's bottom edge.
               | Derived from the PNG's real alpha bbox (canvas 2000x1334, subject
               | x 504→1749, y 225→1300):
               |   • subject is 62.25% of canvas width → element must be 83% of the
               |     card so she lands at the ~51% Figma shows.
               |   • subject's centre sits 6.33% RIGHT of the canvas centre, so a
               |     plain -translate-x-1/2 leaves her off-centre by ~64px. Shifting
               |     to -56.33% (= -50% - 6.33%) centres the SUBJECT, not the box. --}}
            <img src="/images/admissions/hero-girl-books.png" alt=""
                 class="pointer-events-none absolute -bottom-[98px] left-1/2 z-10 hidden w-[83%] -translate-x-[56.33%] object-contain lg:block">
        </div>
    </section>

    {{-- ============================================================
     | ONE LIST / ENROL
     ============================================================ --}}
    {{-- items-end: the pink arch and the kids row both sit FLUSH to the section's
       | bottom edge, meeting the Classes section below (as in the design). --}}
    <section id="enrol" class="relative">
        {{-- px matches the hero so the copy lines up with the yellow card's left edge --}}
        <div class="mx-auto grid max-w-[1600px] items-end gap-10 px-4 pt-16 lg:grid-cols-2 lg:px-8 lg:pt-20">

            {{-- Left: copy → CTA → kids row --}}
            <div>
                <p class="max-w-xl text-lg leading-relaxed lg:text-[21px]">
                    <span class="text-wodi-ink">All enrolments are processed through One list, the government's centralised early childhood registration portal.</span>
                    <span class="text-wodi-muted">Click below to begin your child's enrolment journey. Our team is here to guide you every step of the way.</span>
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    {{-- trusted-by pill --}}
                    <div class="inline-flex items-center gap-3 rounded-full bg-white py-1.5 pr-5 pl-2 shadow-sm ring-1 ring-wodi-pink/15">
                        <div class="flex -space-x-2">
                            @for ($i = 1; $i <= 4; $i++)
                                <img src="/images/admissions/class-{{ $i }}.jpg" alt=""
                                     class="size-7 rounded-full border-2 border-white object-cover">
                            @endfor

                            <span class="grid size-7 place-items-center rounded-full border-2 border-white bg-wodi-pink text-white">
                                <x-lucide-plus class="size-3.5" />
                            </span>
                        </div>

                        <p class="text-[11px] leading-tight font-bold text-wodi-pink">
                            Trusted by<br>Parents and ward alike
                        </p>
                    </div>

                    <a href="#"
                       class="rounded-full bg-wodi-pink px-7 py-3 text-sm font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                        Enroll Now
                    </a>
                </div>

                {{-- Kids row, sitting on the section's bottom edge.
                   | drop-shadow (not box-shadow): it's a transparent cutout, so the
                   | shadow must follow the alpha channel, not the image's bounding box.
                   | Figma: 0px 4px 19.5px #00000040  →  black @ 25% --}}
                <img src="/images/admissions/kids-row.png" alt="Smiling children"
                     class="mt-12 block w-full max-w-[870px] object-contain drop-shadow-[0_4px_19.5px_rgba(0,0,0,0.25)]">
            </div>

            {{-- Right: tall pink arch, flush to the bottom --}}
            <div class="flex justify-center lg:justify-end">
                <div class="h-[340px] w-full max-w-[600px] rounded-t-[12rem] bg-wodi-pink lg:h-[560px]"></div>
            </div>
        </div>
    </section>

    {{-- ============================================================
     | AGE-APPROPRIATE CLASSES
     ============================================================ --}}
    @php
        $classes = [
            ['name' => 'Sunshine Tots',    'time' => '9:00AM', 'seats' => '30', 'age' => '3-4years'],
            ['name' => 'Little Explorers', 'time' => '9:00AM', 'seats' => '10', 'age' => '3-4years'],
            ['name' => 'Bright Beginners', 'time' => '9:00AM', 'seats' => '09', 'age' => '3-4years'],
            ['name' => 'Baby Pampers',     'time' => '9:00AM', 'seats' => '25', 'age' => '3-4years'],
            ['name' => 'PreSchool',        'time' => '9:00AM', 'seats' => '18', 'age' => '3-4years'],
        ];
    @endphp

    <section class="bg-wodi-petal py-16 lg:py-20">
        <div class="mx-auto max-w-[1400px] px-5 lg:px-8">
            <span class="inline-block rounded-full bg-white px-4 py-1 text-xs font-medium text-wodi-pink">
                Programs and Classes
            </span>

            <div class="mt-6 flex items-end justify-between gap-6">
                <div>
                    <h2 class="max-w-md text-2xl leading-snug font-bold lg:text-[32px]">
                        Age-appropriate classes designed for every stage
                    </h2>

                    <p class="mt-3 max-w-md text-xs leading-relaxed text-wodi-muted">
                        Our programs are carefully structured to support children's growth, learning ability,
                        and confidence based on their developmental stage.
                    </p>
                </div>

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

            <div class="no-scrollbar mt-8 flex snap-x snap-mandatory gap-4 overflow-x-auto pb-2 lg:grid lg:grid-cols-5 lg:overflow-visible">
                @foreach ($classes as $i => $class)
                    <article class="w-[220px] shrink-0 snap-start rounded-2xl bg-white p-4 text-center lg:w-auto">
                        <img src="/images/admissions/class-{{ $i + 1 }}.jpg" alt="{{ $class['name'] }}"
                             class="mx-auto size-20 rounded-full object-cover">

                        <h3 class="mt-4 text-sm font-bold">{{ $class['name'] }}</h3>

                        <dl class="mt-4 flex justify-between border-t border-wodi-ink/10 pt-3 text-center">
                            @foreach (['Time' => $class['time'], 'Seats' => $class['seats'], 'Age' => $class['age']] as $label => $value)
                                <div class="flex-1">
                                    <dt class="text-[9px] text-wodi-muted">{{ $label }}</dt>
                                    <dd class="mt-0.5 text-[10px] font-semibold">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>

                        <button type="button"
                                class="mt-4 w-full rounded-full border border-wodi-ink/15 py-2 text-[11px] font-medium transition-colors hover:border-wodi-pink hover:text-wodi-pink">
                            Enrol Class
                        </button>
                    </article>
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <a href="#enrol"
                   class="inline-block rounded-full bg-wodi-pink px-7 py-3 text-sm font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                    Enroll Your Child Today
                </a>
            </div>
        </div>
    </section>

    {{-- ============================================================
     | OUR AMAZING PROGRAMS (tabbed)
     ============================================================ --}}
    @php
        $programs = [
            [
                'tab'   => 'Toddlers Specials',
                'title' => 'Toddlers',
                'age'   => '18months - 3years',
                'body'  => 'Our infant program is all about providing cozy care and fun experiences for your little one right from the start. We use gentle play to help babies safely explore their world while building trust, curiosity, and confidence.',
                'tone'  => 'bg-wodi-pink',
            ],
            [
                'tab'   => 'Toddlers Specials',
                'title' => 'Little Explorers',
                'age'   => '3 - 4years',
                'body'  => 'Our infant program is all about providing cozy care and fun experiences for your little one right from the start. We use gentle play to help babies safely explore their world while building trust, curiosity, and confidence.',
                'tone'  => 'bg-wodi-orange',
            ],
            [
                'tab'   => 'Toddlers Specials',
                'title' => 'PreSchool',
                'age'   => '4 - 5years',
                'body'  => 'Our infant program is all about providing cozy care and fun experiences for your little one right from the start. We use gentle play to help babies safely explore their world while building trust, curiosity, and confidence.',
                'tone'  => 'bg-wodi-yellow',
            ],
        ];

        $activities = [
            ['label' => 'Curious Play',     'dot' => 'bg-wodi-yellow'],
            ['label' => 'Creative learning','dot' => 'bg-wodi-teal'],
            ['label' => 'Nurture',          'dot' => 'bg-wodi-teal'],
            ['label' => 'Kids Growth',      'dot' => 'bg-wodi-pink'],
            ['label' => 'Creative Play',    'dot' => 'bg-wodi-green'],
        ];
    @endphp

    <section x-data="{ tab: 0 }" class="bg-wodi-blush py-16 lg:py-20">
        <div class="mx-auto max-w-[1100px] px-5 text-center lg:px-8">
            <h2 class="text-3xl font-bold lg:text-[36px]">Our amazing programs</h2>

            <p class="mx-auto mt-3 max-w-md text-xs leading-relaxed text-wodi-muted">
                Our caring approach and thoughtful designed programs set us apart, with small class sizes
                that ensure personal attention for every child
            </p>

            {{-- tabs --}}
            <div class="mt-7 flex flex-wrap justify-center gap-3">
                @foreach ($programs as $i => $program)
                    <button type="button"
                            @click="tab = {{ $i }}"
                            :class="tab === {{ $i }} ? '{{ $program['tone'] }} text-white' : 'bg-white text-wodi-ink hover:bg-white/70'"
                            class="rounded-full px-6 py-2 text-xs font-medium transition-colors">
                        {{ $program['tab'] }}
                    </button>
                @endforeach
            </div>

            {{-- panels --}}
            @foreach ($programs as $i => $program)
                <div x-show="tab === {{ $i }}" x-cloak x-transition.opacity
                     class="mt-10 grid gap-8 rounded-3xl bg-white p-5 text-left md:grid-cols-2 lg:p-6">

                    @php $img = "images/admissions/program-" . ($i + 1) . ".jpg"; @endphp

                    @if (file_exists(public_path($img)))
                        <img src="/{{ $img }}" alt="{{ $program['title'] }}"
                             class="h-full min-h-[240px] w-full rounded-2xl object-cover">
                    @else
                        <span class="min-h-[240px] w-full rounded-2xl bg-neutral-200"></span>
                    @endif

                    <div class="py-2">
                        <div class="flex items-baseline gap-3">
                            <h3 class="text-2xl font-bold">{{ $program['title'] }}</h3>
                            <span class="text-xs text-wodi-muted">{{ $program['age'] }}</span>
                        </div>

                        <p class="mt-4 text-xs leading-relaxed text-wodi-muted">{{ $program['body'] }}</p>

                        <p class="mt-6 text-sm font-bold">Key Activities Include:</p>

                        <ul class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2">
                            @foreach ($activities as $activity)
                                <li class="flex items-center gap-2 text-[11px] text-wodi-ink">
                                    <span class="{{ $activity['dot'] }} size-2 shrink-0 rounded-full"></span>
                                    {{ $activity['label'] }}
                                </li>
                            @endforeach
                        </ul>

                        <a href="#enrol"
                           class="mt-6 inline-block rounded-full bg-wodi-pink px-7 py-2.5 text-xs font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                            Get Started
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============================================================
     | 3-UP CTA ROW
     ============================================================ --}}
    <section class="bg-wodi-blush pb-16 lg:pb-20">
        <div class="mx-auto grid max-w-[1100px] gap-5 px-5 md:grid-cols-3 lg:px-8">
            <img src="/images/admissions/classroom-girl.jpg" alt="Pupil in a classroom"
                 class="h-64 w-full rounded-2xl object-cover lg:h-72">

            {{-- middle: pink card with cutout --}}
            <div class="relative flex h-64 flex-col overflow-hidden rounded-2xl border-2 border-wodi-pink bg-white p-6 lg:h-72">
                <h3 class="relative z-10 max-w-[9rem] text-base leading-snug font-bold text-wodi-pink">
                    Ready to give your child the best start?
                </h3>

                <a href="#enrol"
                   class="relative z-10 mt-3 self-start rounded-full bg-wodi-pink px-5 py-2 text-[11px] font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                    Enrol Now!
                </a>

                <img src="/images/admissions/thumbs-up.png" alt=""
                     class="pointer-events-none absolute -right-2 bottom-0 w-40 object-contain lg:w-44">
            </div>

            <img src="/images/admissions/kids-tablets.jpg" alt="Pupils giving a thumbs up"
                 class="h-64 w-full rounded-2xl object-cover lg:h-72">
        </div>
    </section>

    @include('partials.newsletter')

@endsection
