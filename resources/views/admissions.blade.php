@extends('layouts.site')

@section('title', 'Admissions — ' . config('app.name'))
@section('meta_description', "All enrolments are processed through One List, the government's centralised early childhood registration portal.")

@section('content')

    {{-- ============================================================
     | HERO — yellow card
     ============================================================ --}}
    <section class="px-4 pt-24 lg:pt-28">
        <div class="relative mx-auto max-w-[1600px] overflow-hidden rounded-3xl bg-wodi-yellow">
            {{-- faint doodle pattern --}}
            <img src="/images/admissions/doodle-pattern.png" alt=""
                 class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-[0.06]">

            <div class="relative grid items-center gap-6 px-8 py-10 lg:grid-cols-[1fr_auto_1fr] lg:px-14 lg:py-12">

                {{-- Left column --}}
                <div class="relative z-10">
                    <h1 class="max-w-xs text-3xl leading-tight font-extrabold text-wodi-ink lg:text-[38px]">
                        Ready to get Started? Enrolment is easy.
                    </h1>

                    {{-- dotted connector (desktop) --}}
                    <div class="my-8 hidden h-24 border-l-2 border-dashed border-wodi-ink/60 lg:block"></div>

                    <div class="mt-8 lg:mt-0">
                        <p class="font-bold text-wodi-ink">Hello There!</p>
                        <p class="mt-1 max-w-[15rem] text-xs leading-relaxed text-wodi-ink/80">
                            Have questions about the process?
                            <a href="#" class="font-semibold underline underline-offset-2">Contact us</a>
                            — we're happy to help you navigate enrolment.
                        </p>
                    </div>
                </div>

                {{-- Centre: girl on books --}}
                <div class="relative z-10 flex justify-center">
                    <img src="/images/admissions/hero-girl-books.png"
                         alt="Pupil leaning on a stack of books"
                         class="w-full max-w-md object-contain lg:max-w-lg">
                </div>

                {{-- Right column --}}
                <div class="relative z-10 flex flex-col items-start gap-6 lg:items-end lg:self-start lg:pt-4">
                    <div class="lg:text-right">
                        <p class="max-w-xs text-xs leading-relaxed font-medium text-wodi-ink">
                            Securing your child's spot at WODI Daycare begins with the government's official
                            placement system — OneList Waterloo Region.
                        </p>

                        <a href="#enrol"
                           class="mt-4 inline-block rounded-full bg-wodi-pink px-6 py-2.5 text-xs font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                            Get Started
                        </a>
                    </div>

                    {{-- users pill --}}
                    <div class="mt-auto flex items-center gap-4 rounded-full bg-wodi-ink py-2 pr-6 pl-3 lg:self-end">
                        <div class="flex -space-x-2">
                            @for ($i = 1; $i <= 3; $i++)
                                <img src="/images/admissions/class-{{ $i }}.jpg" alt=""
                                     class="size-8 rounded-full border-2 border-wodi-ink object-cover">
                            @endfor

                            <span class="grid size-8 place-items-center rounded-full border-2 border-wodi-ink bg-wodi-pink text-white">
                                <x-lucide-plus class="size-4" />
                            </span>
                        </div>

                        <div class="text-white">
                            <p class="text-lg leading-none font-bold">3k+</p>
                            <p class="text-[10px] text-white/70">Users</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
     | ONE LIST / ENROL
     ============================================================ --}}
    <section id="enrol" class="relative overflow-hidden">
        <div class="mx-auto grid max-w-[1400px] items-start gap-10 px-5 py-16 lg:grid-cols-2 lg:px-8 lg:py-20">
            <div>
                <p class="max-w-xl text-lg leading-relaxed lg:text-xl">
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
            </div>

            {{-- pink arch --}}
            <div class="relative flex justify-center lg:justify-end">
                <div class="h-[240px] w-full max-w-sm rounded-t-[8rem] rounded-b-3xl bg-wodi-pink lg:h-[260px]"></div>
            </div>
        </div>

        {{-- kids row, flush to the section bottom --}}
        <div class="mx-auto max-w-[1400px] px-5 lg:px-8">
            <img src="/images/admissions/kids-row.png" alt="Smiling children"
                 class="w-full max-w-lg object-contain">
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
