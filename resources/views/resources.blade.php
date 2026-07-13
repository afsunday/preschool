@extends('layouts.site')

@section('title', 'Parents & Kids Resource — ' . config('app.name'))
@section('meta_description', 'Helping families continue learning at home through activities, videos, reading materials and helpful links.')

@section('content')

    {{-- ============================================================
     | HERO — three colour cards, each with a cutout breaking out the top
     |
     | Every cutout is placed from its real alpha bbox (measured, not guessed):
     |   kids-cheer : subject 95.8% of canvas, centre  -0.42%
     |   boy-thumbs : subject 47.3% of canvas, centre  -1.52%
     |   girl-wave  : subject 63.7% of canvas, centre  +6.88%
     | The element is sized/offset so the SUBJECT lands centred on its card.
     ============================================================ --}}
    {{--
     | Each cutout's subject runs to the BOTTOM of its PNG canvas, so anchoring the
     | image at bottom-0 lands the subject's feet exactly on the card's bottom edge;
     | the head/arms then break out of the top. Element widths are derived from the
     | real alpha bboxes (heavy transparent padding, so they're far wider than the
     | subject's visual width):
     |   kids-cheer  subject 95.8% of canvas, centre -0.42%  → w 83%
     |   boy-thumbs  subject 47.3% of canvas, centre -1.52%  → w 191%
     |   girl-wave   subject 63.7% of canvas, centre +6.88%  → w 142%
     | translateX = -50% minus the subject's centre offset, so the SUBJECT is centred.
    --}}
    @php
        $heroCards = [
            [
                'img'    => '/images/resources/hero-kids-cheer.png',
                'alt'    => 'Two children cheering',
                'bg'     => 'bg-[#F5344E]',
                'ratio'  => 'aspect-[16/9]',
                'w'      => 'w-[83%]',
                'shiftX' => '-translate-x-[49.6%]',
            ],
            [
                'img'    => '/images/resources/hero-boy-thumbs.png',
                'alt'    => 'Boy giving a thumbs up',
                'bg'     => 'bg-[#22C55E]',
                'ratio'  => 'aspect-square',        // centre card is taller, per Figma
                'w'      => 'w-[191%]',
                'shiftX' => '-translate-x-[48.5%]',
            ],
            [
                'img'    => '/images/resources/hero-girl-wave.png',
                'alt'    => 'Girl waving',
                'bg'     => 'bg-wodi-yellow',
                'ratio'  => 'aspect-[16/9]',
                'w'      => 'w-[142%]',
                'shiftX' => '-translate-x-[56.9%]',
            ],
        ];
    @endphp

    <section class="relative overflow-hidden bg-wodi-blush">
        <img src="/images/patterns/grid.png" alt=""
             class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-60">

        <div class="relative mx-auto max-w-[1300px] px-4 pt-24 pb-16 lg:px-8 lg:pt-28">
            <h1 class="font-heading mx-auto max-w-2xl text-center text-3xl leading-tight font-extrabold text-wodi-pink sm:text-4xl lg:text-[42px]">
                Everything You Need, All in One Place.
            </h1>

            <p class="mx-auto mt-5 max-w-md text-center text-sm leading-relaxed text-wodi-pink lg:text-[15px]">
                Helping families continue learning at home through activities, videos,
                reading materials and helpful lin
            </p>

            {{-- three cards — bottom-aligned, centre one taller --}}
            <div class="mt-36 grid grid-cols-1 items-end gap-x-10 gap-y-36 sm:grid-cols-3 lg:mt-44 lg:gap-x-14">
                @foreach ($heroCards as $card)
                    <div class="relative">
                        <div class="{{ $card['bg'] }} {{ $card['ratio'] }} w-full rounded-2xl"></div>

                        <img src="{{ $card['img'] }}" alt="{{ $card['alt'] }}"
                             class="pointer-events-none absolute bottom-0 left-1/2 {{ $card['w'] }} {{ $card['shiftX'] }} max-w-none object-contain">
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
     | OUR AMAZING PROGRAMS — search + filters + cards
     ============================================================ --}}
    @php
        $filters = ['All', 'Parent Tips', 'Learning Activities', 'Educational Videos', 'Health & Wellness', 'Arts & Craft', 'School Readiness'];

        // action: download | video | read
        $cards = [
            ['img' => '/images/about/program-art.jpg',       'action' => 'download', 'text' => 'Potter ipsum wand elf parchment wingam. Teacup do feint teacup.'],
            ['img' => '/images/about/program-drawing.jpg',   'action' => 'video',    'text' => 'Potter ipsum wand elf parchment wingam. Teacup do feint teacup.'],
            ['img' => '/images/about/program-classroom.jpg', 'action' => 'download', 'text' => 'Potter ipsum wand elf parchment wingam. Teacup do feint teacup.'],
            ['img' => '/images/about/gallery-8.jpg',         'action' => 'read',     'text' => 'The rise of artificial intelligence in the educational sector'],
            ['img' => '/images/about/gallery-3.jpg',         'action' => 'read',     'text' => 'Potter ipsum wand elf parchment wingam. Teacup do feint teacup.'],
            ['img' => '/images/about/gallery-4.jpg',         'action' => 'read',     'text' => 'Potter ipsum wand elf parchment wingam. Teacup do feint teacup.'],
            ['img' => '/images/about/testimonial.jpg',       'action' => 'read',     'text' => 'Potter ipsum wand elf parchment wingam. Teacup do feint teacup.'],
            ['img' => '/images/about/gallery-2.jpg',         'action' => 'read',     'text' => 'Potter ipsum wand elf parchment wingam. Teacup do feint teacup.'],
            ['img' => '/images/about/gallery-6.jpg',         'action' => 'read',     'text' => 'Potter ipsum wand elf parchment wingam. Teacup do feint teacup.'],
        ];

        $labels = ['download' => 'Download File', 'video' => 'Watch Video', 'read' => 'Read'];
        $icons  = ['download' => 'lucide-download', 'video' => 'lucide-play', 'read' => null];
    @endphp

    <section id="programs" x-data="{ filter: 'All' }" class="bg-wodi-blush pb-20">
        <div class="mx-auto max-w-[1400px] px-4 lg:px-8">
            <h2 class="text-center text-3xl font-bold lg:text-[38px]">Our Amazing Programs</h2>

            <p class="mx-auto mt-3 max-w-lg text-center text-sm leading-relaxed text-wodi-muted">
                Our caring approach and thoughtful designed programs set us apart, with small class sizes
                that ensure personal attention for every child
            </p>

            {{-- search --}}
            <form action="#" method="GET" class="mx-auto mt-8 max-w-2xl">
                <div class="flex items-center gap-2 rounded-full bg-white p-1.5 pl-6 shadow-sm">
                    <label for="resource-search" class="sr-only">Search resources</label>

                    <input id="resource-search" name="q" type="search"
                           placeholder="Search resources... titles, descriptions, tags"
                           class="min-w-0 flex-1 bg-transparent py-2.5 text-sm text-wodi-ink placeholder:text-wodi-muted/70 focus:outline-none">

                    <button type="submit"
                            class="shrink-0 rounded-full bg-wodi-pink px-8 py-2.5 text-xs font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                        Search
                    </button>
                </div>
            </form>

            {{-- filter tabs --}}
            <div class="no-scrollbar mx-auto mt-6 flex max-w-4xl justify-start gap-2 overflow-x-auto pb-1 sm:justify-center">
                @foreach ($filters as $f)
                    <button type="button"
                            @click="filter = '{{ $f }}'"
                            :class="filter === '{{ $f }}'
                                ? 'bg-wodi-pink text-white'
                                : 'bg-transparent text-wodi-ink hover:bg-white'"
                            class="shrink-0 rounded-full px-4 py-1.5 text-[11px] font-medium whitespace-nowrap transition-colors">
                        {{ $f }}
                    </button>
                @endforeach
            </div>

            {{-- cards --}}
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($cards as $card)
                    <article class="flex flex-col rounded-2xl bg-white p-3 shadow-sm">
                        <img src="{{ $card['img'] }}" alt=""
                             loading="lazy"
                             class="aspect-[4/3] w-full rounded-xl object-cover">

                        <p class="flex-1 px-2 py-4 text-center text-xs leading-relaxed text-wodi-ink">
                            {{ $card['text'] }}
                        </p>

                        <a href="#"
                           class="mx-auto mb-1 inline-flex items-center gap-1.5 rounded-full border border-wodi-pink px-6 py-2 text-[11px] font-medium text-wodi-pink transition-colors hover:bg-wodi-pink hover:text-white">
                            {{ $labels[$card['action']] }}

                            @if ($icons[$card['action']])
                                <x-dynamic-component :component="$icons[$card['action']]" class="size-3.5" />
                            @endif
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @include('partials.newsletter')

@endsection
