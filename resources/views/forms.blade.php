@extends('layouts.site')

@section('title', 'Forms & Policies — ' . config('app.name'))
@section('meta_description', 'We welcome children from birth to age 12 into our programs. The registration process varies slightly by age group and location.')

@section('content')

    {{-- ============================================================
     | HERO
     ============================================================ --}}
    <section class="relative overflow-hidden bg-wodi-blush">
        <img src="/images/patterns/grid.png" alt=""
             class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-60">

        <div class="relative mx-auto max-w-[1400px] px-4 pt-24 pb-10 text-center lg:px-8 lg:pt-28">
            <h1 class="font-heading mx-auto max-w-xl text-3xl leading-tight font-extrabold text-wodi-pink sm:text-4xl lg:text-[42px]">
                We are here to help every step of the way!
            </h1>

            <p class="mx-auto mt-6 max-w-2xl text-sm leading-relaxed text-wodi-pink lg:text-[15px]">
                Serving our community since 1981, RisingOaks has several locations across the Waterloo Region
                in Ayr, Cambridge, Kitchener and Waterloo. Visit our
                <a href="#" class="underline underline-offset-2 hover:no-underline">location finder</a>
                to find a location near you!
            </p>

            <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-wodi-pink lg:text-[15px]">
                We welcome children from birth to age 12 into our programs. The registration process varies
                slightly by age group and location.
            </p>
        </div>

        {{-- cloud collage (shared partial — same Figma path as the About hero) --}}
        <div class="relative pb-14">
            @include('partials.cloud-image', [
                'src' => '/images/about/hero-collage.jpg',
                'alt' => 'Children playing at WODI Daycare',
                'id'  => 'wodi-cloud-forms',
            ])
        </div>
    </section>

    {{-- ============================================================
     | REGISTRATION FORMS
     ============================================================ --}}
    @php
        $forms = [
            [
                'img'   => '/images/admissions/class-1.jpg',
                'title' => 'Full-day early learning',
                'lines' => ['Infant, Toddler, Preschool', 'Birth to age 5'],
            ],
            [
                'img'   => '/images/admissions/class-3.jpg',
                'title' => 'Before/After school',
                'lines' => ['Cathoic school (WCDSB) locations', 'JK to age 12'],
            ],
            [
                'img'   => '/images/admissions/class-5.jpg',
                'title' => 'Before/After school',
                'lines' => ['Public school(WRDSB) locations', 'JK to age 12'],
            ],
        ];
    @endphp

    <section class="bg-wodi-blush pb-20">
        <div class="mx-auto max-w-[1400px] px-4 lg:px-8">
            {{-- mixed-weight lead --}}
            <p class="max-w-4xl text-2xl leading-snug lg:text-[30px]">
                <span class="text-wodi-muted">Based on your care needs,</span>
                <span class="font-bold text-wodi-ink">select your child's age group and/or location type</span>
                <span class="text-wodi-muted">below for more registration details:</span>
            </p>

            <div class="mx-auto mt-16 grid max-w-[1150px] gap-6 md:grid-cols-3">
                @foreach ($forms as $form)
                    <article class="flex flex-col items-center rounded-2xl bg-white p-8 text-center shadow-sm">
                        <img src="{{ $form['img'] }}" alt="{{ $form['title'] }}"
                             class="size-32 rounded-full object-cover">

                        <h2 class="mt-6 text-base font-bold text-wodi-ink">{{ $form['title'] }}</h2>

                        <div class="mt-3 space-y-1">
                            @foreach ($form['lines'] as $line)
                                <p class="text-xs text-wodi-ink underline underline-offset-2">{{ $line }}</p>
                            @endforeach
                        </div>

                        <a href="#"
                           class="mt-8 block w-full rounded-full border border-wodi-ink/20 py-3.5 text-center text-xs font-medium text-wodi-muted transition-colors hover:border-wodi-pink hover:text-wodi-pink">
                            Download Form
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @include('partials.newsletter')

@endsection
