@extends('layouts.site')

@section('title', config('app.name') . ' — Where little minds come alive')
@section('meta_description', 'WODI is your trusted early childhood partner, nurturing curiosity, building confidence, and giving every child the best start in life.')

@section('content')

    {{-- ============================================================
     | HERO
     ============================================================ --}}
    <section
        x-data="{
            y: 0,
            reduce: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            shift(factor) { return this.reduce ? 0 : this.y * factor; },
        }"
        @scroll.window.passive="y = window.scrollY"
        class="relative overflow-hidden">

        {{-- deepest layer: lags behind the scroll --}}
        <img src="/images/patterns/grid.png" alt=""
             :style="`transform: translate3d(0, ${shift(0.25)}px, 0)`"
             class="pointer-events-none absolute inset-x-0 top-0 h-[120%] w-full object-cover opacity-60 will-change-transform">

        {{-- scattered doodles — varied speeds for depth (desktop only) --}}
        <img src="/images/doodles/pen.png" alt="" :style="`transform: translate3d(0, ${shift(0.32)}px, 0)`"
             class="pointer-events-none absolute top-24 left-6 hidden w-8 will-change-transform lg:block">
        <img src="/images/doodles/planet-outline.png" alt="" :style="`transform: translate3d(0, ${shift(0.18)}px, 0)`"
             class="pointer-events-none absolute top-36 left-[16%] hidden w-9 will-change-transform lg:block">
        <img src="/images/doodles/swirl.png" alt="" :style="`transform: translate3d(0, ${shift(0.4)}px, 0)`"
             class="pointer-events-none absolute top-32 right-[24%] hidden w-7 will-change-transform lg:block">
        <img src="/images/doodles/planet-ringed.png" alt="" :style="`transform: translate3d(0, ${shift(0.22)}px, 0)`"
             class="pointer-events-none absolute top-24 right-8 hidden w-10 will-change-transform lg:block">
        <img src="/images/doodles/build.png" alt="" :style="`transform: translate3d(0, ${shift(-0.14)}px, 0)`"
             class="pointer-events-none absolute top-[42%] left-[22%] hidden w-7 will-change-transform lg:block">
        <img src="/images/doodles/hub.png" alt="" :style="`transform: translate3d(0, ${shift(-0.2)}px, 0)`"
             class="pointer-events-none absolute right-[10%] bottom-[42%] hidden w-8 will-change-transform lg:block">
        <img src="/images/doodles/planet-blue.png" alt="" :style="`transform: translate3d(0, ${shift(0.3)}px, 0)`"
             class="pointer-events-none absolute right-6 bottom-24 hidden w-10 will-change-transform lg:block">
        <img src="/images/doodles/earth.png" alt="" :style="`transform: translate3d(0, ${shift(0.16)}px, 0)`"
             class="pointer-events-none absolute right-[18%] bottom-16 hidden w-9 will-change-transform lg:block">
        <img src="/images/doodles/spaceship.png" alt="" :style="`transform: translate3d(0, ${shift(0.36)}px, 0)`"
             class="pointer-events-none absolute bottom-20 left-8 hidden w-10 will-change-transform lg:block">

        {{-- pt clears the fixed navbar --}}
        <div class="relative mx-auto max-w-[1400px] px-5 pt-28 pb-16 lg:px-8 lg:pt-32 lg:pb-24">
            <div class="grid items-center gap-10 lg:grid-cols-[1fr_minmax(0,640px)_1fr]">

                {{-- foreground: moves slightly faster than the page --}}
                <div class="order-2 hidden justify-center lg:order-1 lg:flex"
                     :style="`transform: translate3d(0, ${shift(-0.06)}px, 0)`">
                    <img src="/images/home/hero-girl.png" alt="Smiling pupil with a backpack"
                         class="w-full max-w-[300px] object-contain will-change-transform">
                </div>

                <div class="order-1 text-center lg:order-2"
                     :style="`transform: translate3d(0, ${shift(0.06)}px, 0)`">
                    <h1 class="text-4xl leading-tight font-extrabold text-wodi-pink sm:text-5xl lg:text-[52px]">
                        Where little minds come alive
                    </h1>

                    <p class="mt-4 text-[15px] text-wodi-muted">
                        Let the children be the director, and the actor in their own play
                    </p>

                    <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                        <a href="#admissions"
                           class="rounded-full bg-wodi-pink px-7 py-3.5 text-sm font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                            Enroll Your Child Today
                        </a>

                        <a href="#resources"
                           class="rounded-full border border-wodi-pink px-7 py-3.5 text-sm font-medium text-wodi-pink transition-colors hover:bg-wodi-pink hover:text-white">
                            Discover Our Programs
                        </a>
                    </div>

                    <p class="mx-auto mt-8 max-w-md text-[15px] leading-relaxed font-medium text-wodi-pink">
                        WODI is your trusted early childhood partner, nurturing curiosity,
                        building confidence, and giving every child the best start in life.
                    </p>

                    {{-- Stats --}}
                    <div class="mx-auto mt-10 flex max-w-lg items-start justify-between gap-6">
                        <div class="flex items-start gap-2 text-left">
                            <x-lucide-graduation-cap class="mt-1 size-5 shrink-0 text-wodi-pink" />

                            <div>
                                <p class="text-2xl font-extrabold text-wodi-pink">7.5K+</p>
                                <p class="mt-0.5 max-w-[9rem] text-xs leading-snug text-wodi-muted">
                                    Total active students taking gifted courses
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2 self-end text-left">
                            <x-lucide-book-marked class="mt-1 size-5 shrink-0 text-wodi-pink" />

                            <div>
                                <p class="text-2xl font-extrabold text-wodi-pink">50+</p>
                                <p class="mt-0.5 max-w-[9rem] text-xs leading-snug text-wodi-muted">
                                    Available field programs and increasing
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-3 hidden justify-center lg:flex"
                     :style="`transform: translate3d(0, ${shift(-0.1)}px, 0)`">
                    <img src="/images/home/hero-boy.png" alt="Pupil in school uniform"
                         class="w-full max-w-[280px] object-contain will-change-transform">
                </div>
            </div>

            {{-- Mobile children --}}
            <div class="mt-10 flex items-end justify-center gap-4 lg:hidden">
                <img src="/images/home/hero-girl.png" alt="" class="w-1/2 max-w-[180px] object-contain">
                <img src="/images/home/hero-boy.png" alt="" class="w-1/2 max-w-[160px] object-contain">
            </div>
        </div>
    </section>

    {{-- ============================================================
     | FEATURE BAR
     ============================================================ --}}
    <section class="relative mx-auto max-w-[1400px] px-5 lg:px-8">
        <div class="grid gap-8 rounded-[28px] bg-wodi-maroon px-8 py-10 text-white lg:grid-cols-4 lg:items-center lg:gap-4 lg:px-12">

            <div class="lg:pr-8">
                <h2 class="text-3xl font-extrabold lg:text-[32px]">Safe &amp; Regulated</h2>
                <p class="mt-3 text-sm leading-relaxed text-white/80">
                    Fully compliant with all government standards for early childhood care
                </p>
            </div>

            <div class="lg:border-l lg:border-white/15 lg:pl-8">
                <x-lucide-boxes class="size-8" />
                <h3 class="mt-4 text-lg font-bold">Built to Develop</h3>
                <p class="mt-2 text-sm leading-relaxed text-white/80">
                    Play-based curriculum aligned to developmental milestones
                </p>
            </div>

            {{-- highlighted --}}
            <div class="rounded-2xl bg-wodi-rose p-6">
                <x-lucide-users class="size-8" />
                <h3 class="mt-4 text-lg font-bold">Partner to Parents</h3>
                <p class="mt-2 text-sm leading-relaxed text-white/90">
                    Regular updates and real involvement in your child's journey
                </p>
            </div>

            <div class="lg:border-l lg:border-white/15 lg:pl-8">
                <x-lucide-megaphone class="size-8" />
                <h3 class="mt-4 text-lg font-bold">Government<br class="hidden lg:block"> Approved</h3>

                <a href="#" class="mt-2 inline-block text-sm text-white/80 underline-offset-4 hover:underline">
                    Learn more
                </a>
            </div>
        </div>
    </section>

    {{-- ============================================================
     | ABOUT
     ============================================================ --}}
    <section id="about" class="mx-auto max-w-[1400px] px-5 py-16 lg:px-8 lg:py-20">
        <span class="inline-block rounded-full border border-wodi-pink/40 px-4 py-1 text-xs font-medium text-wodi-pink">
            About us
        </span>

        <p class="mt-6 max-w-5xl text-2xl leading-relaxed font-medium lg:text-[30px] lg:leading-[1.45]">
            <span class="text-wodi-ink">At WODI's daycare, we believe childhood should be joyful, meaningful, and full of wonder.</span>
            <span class="text-wodi-muted">Our caring and experienced teachers nurture creativity confidence, and curiosity every single day.</span>
            <span class="text-wodi-ink">Through play based learning and guided exploration we help.</span>
        </p>

        {{-- Trusted-by pill --}}
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

                <span class="grid size-8 place-items-center rounded-full border-2 border-white bg-wodi-purple text-white">
                    <x-lucide-plus class="size-4" />
                </span>
            </div>

            <p class="text-xs leading-tight font-bold text-wodi-purple">
                Trusted by<br>thousands of users
            </p>
        </div>

        <img src="/images/home/kids.png" alt="Teacher and children doing craft activities"
             class="mt-8 h-[220px] w-full rounded-3xl object-cover sm:h-[300px] lg:h-[380px]">
    </section>

    {{-- ============================================================
     | OUR RESOURCES
     ============================================================ --}}
    @php
        $resources = ['bg-wodi-teal', 'bg-wodi-yellow', 'bg-wodi-orange', 'bg-wodi-purple', 'bg-wodi-green'];
    @endphp

    <section id="resources" class="mx-auto max-w-[1400px] px-5 pb-20 lg:px-8">
        <div class="flex items-end justify-between gap-6">
            <div>
                <h2 class="text-2xl font-bold lg:text-[28px]">Our resources</h2>
                <p class="mt-1 text-sm text-wodi-muted">We develop their confidence to make them sharper</p>
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

        <div class="no-scrollbar mt-6 flex snap-x snap-mandatory gap-5 overflow-x-auto pb-2">
            @foreach ($resources as $accent)
                <article class="{{ $accent }} flex w-[260px] shrink-0 snap-start flex-col gap-4 rounded-[26px] p-3">
                    <div class="h-[190px] rounded-[20px] bg-white"></div>

                    <button type="button"
                            class="mx-auto inline-flex items-center gap-1.5 rounded-full bg-white px-4 py-1.5 text-[11px] font-medium text-wodi-ink shadow-sm">
                        download
                        <x-lucide-download class="size-3.5" />
                    </button>
                </article>
            @endforeach
        </div>
    </section>

    {{-- ============================================================
     | TRADITIONAL IN-PERSON CLASSROOM
     ============================================================ --}}
    <section class="relative overflow-hidden bg-white py-16 lg:py-20">
        <img src="/images/patterns/grid.png" alt=""
             class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-50">

        <img src="/images/doodles/heart.png" alt="" class="pointer-events-none absolute top-10 left-6 hidden w-9 lg:block">
        <img src="/images/doodles/school.png" alt="" class="pointer-events-none absolute bottom-16 left-[8%] hidden w-9 lg:block">
        <img src="/images/doodles/school-bus.png" alt="" class="pointer-events-none absolute bottom-8 left-[32%] hidden w-10 lg:block">

        <div class="relative mx-auto grid max-w-[1100px] items-center gap-10 px-5 lg:grid-cols-2 lg:px-8">
            <div>
                <h2 class="max-w-md text-3xl leading-snug font-extrabold lg:text-[34px]">
                    Traditional in-person classroom encouraging students
                </h2>

                <ul class="mt-8 space-y-5">
                    @foreach ([
                        'Focus for longer periods of time',
                        'Engage with their peers',
                        'Understanding of complicated concepts',
                    ] as $point)
                        <li class="flex items-center gap-3">
                            <span class="grid size-6 shrink-0 place-items-center rounded-full bg-wodi-pink text-white">
                                <x-lucide-check class="size-3.5" stroke-width="3" />
                            </span>

                            <span class="text-[15px] font-semibold">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="relative flex justify-center">
                <span class="absolute top-1/2 left-1/2 aspect-square w-[300px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-wodi-blue sm:w-[340px]"></span>

                <img src="/images/home/kid-with-book.png" alt="Student holding books"
                     class="relative w-[260px] object-contain sm:w-[300px]">

                <span class="absolute top-4 right-6 grid size-9 place-items-center rounded-full bg-white shadow-md">
                    <x-lucide-heart class="size-4 fill-wodi-pink text-wodi-pink" />
                </span>
            </div>
        </div>
    </section>

    {{-- ============================================================
     | ADMISSIONS & ENROLMENT
     ============================================================ --}}
    @php
        $steps = [
            [
                'title' => 'Register on One List',
                'icon'  => 'lucide-user-round',
                'body'  => 'Potter ipsum wand elf parchment wingardium. Years mcgonagall bonnet locomotor blood basilisk wheels floating. Veil polyjuice levicorpus ipsum cup horntail dagger bertie.',
            ],
            [
                'title' => 'Select WODI Daycare',
                'icon'  => 'lucide-square-pen',
                'body'  => 'Potter ipsum wand elf parchment wingardium. Fire-whisky ginny back stairs crimson. Elder for mr harry suck fairy rock-cake ridgeback. Where downfall downfall gillywater bertie.',
            ],
            [
                'title' => 'Get a Spot',
                'icon'  => 'lucide-id-card',
                'body'  => 'Potter ipsum wand elf parchment wingardium. Knew crimson his map dress crush owl find. His cannot treats phials got nick sir me trace for. Impedimenta the will trevor squashy sticking.',
            ],
        ];
    @endphp

    <section id="admissions" class="bg-white py-16 lg:py-20">
        <div class="mx-auto max-w-[1400px] px-5 lg:px-8">
            <h2 class="text-3xl font-bold lg:text-[38px]">Admissions &amp; Enrolment</h2>
            <p class="mt-2 text-[15px] text-wodi-muted">Reduce drop-off at the redirect. Prepare the user before they leave the site.</p>

            <div class="mt-14 grid gap-12 lg:grid-cols-3 lg:gap-6">
                @foreach ($steps as $index => $step)
                    <div class="relative flex flex-col items-center text-center">
                        @if (! $loop->last)
                            <span class="absolute top-12 left-[calc(50%+3.5rem)] right-[calc(-50%+3.5rem)] hidden h-px bg-wodi-pink/25 lg:block"></span>
                        @endif

                        <div class="relative">
                            <span class="grid size-24 place-items-center rounded-full bg-wodi-pink/10">
                                <x-dynamic-component :component="$step['icon']" class="size-9 text-wodi-pink" />
                            </span>

                            <span class="absolute -top-1 -left-1 grid size-7 place-items-center rounded-full bg-wodi-pink text-xs font-bold text-white">
                                {{ $index + 1 }}
                            </span>
                        </div>

                        <h3 class="mt-6 text-lg font-bold">{{ $step['title'] }}</h3>
                        <p class="mt-3 max-w-xs text-sm leading-relaxed text-wodi-muted">{{ $step['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
     | GROWING MINDS BANNER
     ============================================================ --}}
    <section class="py-16 lg:py-20">
        <div class="mx-auto max-w-[1200px] px-5 lg:px-8">
            <div class="grid overflow-hidden rounded-[32px] md:grid-cols-2">
                <img src="/images/home/kids-smile.png" alt="Excited children raising their hands"
                     class="h-64 w-full object-cover md:h-full">

                <div class="flex flex-col justify-center bg-wodi-pink px-8 py-10 text-white lg:px-12">
                    <h2 class="text-2xl leading-snug font-extrabold lg:text-[32px]">
                        Growing minds, active brains. Children will enjoy a fun-filled games and online activities
                    </h2>

                    <p class="mt-5 text-sm leading-relaxed text-white/90">
                        Children will be tested with their knowledge skills via trivia games for kids activities like never before
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
     | TESTIMONIALS
     ============================================================ --}}
    <section class="relative overflow-hidden bg-white py-16 lg:py-20">
        <img src="/images/patterns/grid.png" alt=""
             class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-50">

        <div class="relative mx-auto max-w-[1200px] px-5 lg:px-8">
            <h2 class="text-center text-3xl font-bold lg:text-[34px]">
                What <span class="text-wodi-pink">parents</span> say
            </h2>

            <div class="mt-12 flex items-center gap-6">
                <button type="button" aria-label="Previous testimonial"
                        class="hidden size-10 shrink-0 place-items-center rounded-full border border-wodi-pink text-wodi-pink transition-colors hover:bg-wodi-pink hover:text-white lg:grid">
                    <x-lucide-arrow-left class="size-4" />
                </button>

                <figure class="grid flex-1 items-center gap-8 md:grid-cols-2">
                    <img src="/images/home/testimonial-photo.png" alt="Teacher painting with children"
                         class="h-[280px] w-full rounded-3xl object-cover">

                    <div>
                        <blockquote class="text-lg leading-relaxed text-wodi-ink lg:text-xl">
                            Potter ipsum wand elf parchment wingardium. Potter do seven dervish flat red hiya.
                            12 us lorem mimbletonia tonight broomstick must shunpike.
                        </blockquote>

                        <figcaption class="mt-7 flex items-center gap-3">
                            @if (file_exists(public_path('images/home/testimonial-avatar.png')))
                                <img src="/images/home/testimonial-avatar.png" alt=""
                                     class="size-11 rounded-full object-cover">
                            @else
                                <span class="grid size-11 place-items-center rounded-full bg-wodi-cream">
                                    <x-lucide-user class="size-5 text-wodi-pink/50" />
                                </span>
                            @endif

                            <div>
                                <p class="font-bold">Loretta Renner</p>
                                <p class="text-sm text-wodi-muted">Anna's Mum</p>
                            </div>
                        </figcaption>
                    </div>
                </figure>

                <button type="button" aria-label="Next testimonial"
                        class="hidden size-10 shrink-0 place-items-center rounded-full border border-wodi-pink text-wodi-pink transition-colors hover:bg-wodi-pink hover:text-white lg:grid">
                    <x-lucide-arrow-right class="size-4" />
                </button>
            </div>
        </div>
    </section>

    @include('partials.newsletter')

@endsection
