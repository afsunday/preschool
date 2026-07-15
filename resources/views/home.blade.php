{{--
 | HOME PAGE BLOCK LIBRARY
 | Each entry declares a CMS block: a schema plus its template, parsed by
 | the registry. Blocks here are available to any page. Not a rendered page.
--}}

@block([
    'key' => 'home_hero',
    'name' => 'Home Hero',
    'group' => 'Home',
    'version' => 1,
    'fields' => [
        ['id' => 'title', 'type' => 'text', 'required' => true],
        ['id' => 'subtitle', 'type' => 'text'],
        ['id' => 'lead', 'type' => 'textarea'],
        ['id' => 'primary_label', 'type' => 'text'],
        ['id' => 'primary_url', 'type' => 'url'],
        ['id' => 'secondary_label', 'type' => 'text'],
        ['id' => 'secondary_url', 'type' => 'url'],
        ['id' => 'left_image', 'type' => 'media', 'kind' => 'image'],
        ['id' => 'right_image', 'type' => 'media', 'kind' => 'image'],
        ['id' => 'stats', 'type' => 'repeater', 'fields' => [
            ['id' => 'value', 'type' => 'text'],
            ['id' => 'label', 'type' => 'text'],
        ]],
    ],
])

@php
    $left = $s->mediaUrl('left_image') ?? '/images/home/hero-girl.png';
    $right = $s->mediaUrl('right_image') ?? '/images/home/hero-boy.png';
    $stats = $s->get('stats', []);
@endphp

<section
    x-data="{ y: 0, reduce: window.matchMedia('(prefers-reduced-motion: reduce)').matches, shift(f){ return this.reduce ? 0 : this.y*f; } }"
    @scroll.window.passive="y = window.scrollY"
    class="relative overflow-hidden">

    <img src="/images/patterns/grid.png" alt=""
         :style="`transform: translate3d(0, ${shift(0.25)}px, 0)`"
         class="pointer-events-none absolute inset-x-0 top-0 h-[120%] w-full object-cover opacity-60 will-change-transform">

    @foreach ([
        ['pen', 'top-24 left-6 w-8', 0.32], ['planet-outline', 'top-36 left-[16%] w-9', 0.18],
        ['swirl', 'top-32 right-[24%] w-7', 0.4], ['planet-ringed', 'top-24 right-8 w-10', 0.22],
        ['planet-blue', 'right-6 bottom-24 w-10', 0.3], ['earth', 'right-[18%] bottom-16 w-9', 0.16],
        ['spaceship', 'bottom-20 left-8 w-10', 0.36],
    ] as [$doodle, $pos, $speed])
        <img src="/images/doodles/{{ $doodle }}.png" alt="" :style="`transform: translate3d(0, ${shift({{ $speed }})}px, 0)`"
             class="pointer-events-none absolute {{ $pos }} hidden will-change-transform lg:block">
    @endforeach

    <div class="relative mx-auto max-w-[1400px] px-5 pt-28 pb-16 lg:px-8 lg:pt-32 lg:pb-24">
        <div class="grid items-center gap-10 lg:grid-cols-[1fr_minmax(0,640px)_1fr]">
            <div class="order-2 hidden justify-center lg:order-1 lg:flex" :style="`transform: translate3d(0, ${shift(-0.06)}px, 0)`">
                <img src="{{ $left }}" alt="" class="w-full max-w-[300px] object-contain will-change-transform">
            </div>

            <div class="order-1 text-center lg:order-2" :style="`transform: translate3d(0, ${shift(0.06)}px, 0)`">
                <h1 class="font-heading text-4xl leading-tight font-extrabold text-wodi-pink sm:text-5xl lg:text-[52px]">
                    {{ $s->get('title') }}
                </h1>

                @if ($s->get('subtitle'))
                    <p class="mt-4 text-[15px] text-wodi-muted">{{ $s->get('subtitle') }}</p>
                @endif

                <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                    @if ($s->get('primary_label'))
                        <a href="{{ $s->get('primary_url', '#') }}"
                           class="rounded-full bg-wodi-pink px-7 py-3.5 text-sm font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                            {{ $s->get('primary_label') }}
                        </a>
                    @endif
                    @if ($s->get('secondary_label'))
                        <a href="{{ $s->get('secondary_url', '#') }}"
                           class="rounded-full border border-wodi-pink px-7 py-3.5 text-sm font-medium text-wodi-pink transition-colors hover:bg-wodi-pink hover:text-white">
                            {{ $s->get('secondary_label') }}
                        </a>
                    @endif
                </div>

                @if ($s->get('lead'))
                    <p class="mx-auto mt-8 max-w-md text-[15px] leading-relaxed font-medium text-wodi-pink">
                        {{ $s->get('lead') }}
                    </p>
                @endif

                @if (count($stats))
                    <div class="mx-auto mt-10 flex max-w-lg items-start justify-between gap-6">
                        @foreach ($stats as $i => $stat)
                            <div class="flex items-start gap-2 text-left {{ $i > 0 ? 'self-end' : '' }}">
                                <x-dynamic-component :component="$i === 0 ? 'lucide-graduation-cap' : 'lucide-book-marked'"
                                                     class="mt-1 size-5 shrink-0 text-wodi-pink" />
                                <div>
                                    <p class="text-2xl font-extrabold text-wodi-pink">{{ data_get($stat, 'value') }}</p>
                                    <p class="mt-0.5 max-w-[9rem] text-xs leading-snug text-wodi-muted">{{ data_get($stat, 'label') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="order-3 hidden justify-center lg:flex" :style="`transform: translate3d(0, ${shift(-0.1)}px, 0)`">
                <img src="{{ $right }}" alt="" class="w-full max-w-[280px] object-contain will-change-transform">
            </div>
        </div>

        <div class="mt-10 flex items-end justify-center gap-4 lg:hidden">
            <img src="{{ $left }}" alt="" class="w-1/2 max-w-[180px] object-contain">
            <img src="{{ $right }}" alt="" class="w-1/2 max-w-[160px] object-contain">
        </div>
    </div>
</section>
@endblock

@block([
    'key' => 'feature_bar',
    'name' => 'Feature Bar',
    'group' => 'Home',
    'version' => 1,
    'fields' => [
        ['id' => 'lead_title', 'type' => 'text'],
        ['id' => 'lead_body', 'type' => 'textarea'],
        ['id' => 'features', 'type' => 'repeater', 'fields' => [
            ['id' => 'icon', 'type' => 'select', 'options' => ['boxes'=>'Boxes','users'=>'Users','megaphone'=>'Megaphone','shield'=>'Shield','sparkles'=>'Sparkles','heart'=>'Heart']],
            ['id' => 'title', 'type' => 'text'],
            ['id' => 'body', 'type' => 'textarea'],
        ]],
    ],
])

@php
    $features = $s->get('features', []);
    $icon = fn ($k) => match ($k) {
        'boxes' => 'lucide-boxes', 'users' => 'lucide-users', 'megaphone' => 'lucide-megaphone',
        'shield' => 'lucide-shield', 'sparkles' => 'lucide-sparkles', 'heart' => 'lucide-heart',
        default => 'lucide-boxes',
    };
@endphp

<section class="relative mx-auto max-w-[1400px] px-5 lg:px-8">
    <div class="grid gap-8 rounded-[28px] bg-wodi-maroon px-8 py-10 text-white lg:grid-cols-4 lg:items-center lg:gap-4 lg:px-12">
        <div class="lg:pr-8">
            <h2 class="text-3xl font-extrabold lg:text-[32px]">{{ $s->get('lead_title') }}</h2>
            <p class="mt-3 text-sm leading-relaxed text-white/80">{{ $s->get('lead_body') }}</p>
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
@endblock

@block([
    'key' => 'about_intro',
    'name' => 'About Intro',
    'group' => 'Home',
    'version' => 1,
    'fields' => [
        ['id' => 'eyebrow', 'type' => 'text'],
        ['id' => 'body', 'type' => 'richtext'],
        ['id' => 'trusted_label', 'type' => 'text'],
        ['id' => 'image', 'type' => 'media', 'kind' => 'image'],
    ],
])

@php
    $image = $s->mediaUrl('image') ?? '/images/home/kids.png';
@endphp

<section class="mx-auto max-w-[1400px] px-5 py-16 lg:px-8 lg:py-20">
    @if ($s->get('eyebrow'))
        <span class="inline-block rounded-full border border-wodi-pink/40 px-4 py-1 text-xs font-medium text-wodi-pink">
            {{ $s->get('eyebrow') }}
        </span>
    @endif

    <div class="prose mt-6 max-w-5xl text-2xl leading-relaxed font-medium lg:text-[30px] lg:leading-[1.45]">
        {!! $s->get('body') !!}
    </div>

    <div class="mt-8 inline-flex items-center gap-3 rounded-full bg-white py-2 pr-5 pl-2 shadow-sm">
        <div class="flex -space-x-2">
            @for ($i = 1; $i <= 4; $i++)
                @if (file_exists(public_path("images/home/avatar-{$i}.png")))
                    <img src="/images/home/avatar-{{ $i }}.png" alt="" class="size-8 rounded-full border-2 border-white object-cover">
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
        <p class="text-xs leading-tight font-bold text-wodi-purple">{!! nl2br(e($s->get('trusted_label', "Trusted by\nthousands of users"))) !!}</p>
    </div>

    <img src="{{ $image }}" alt="" class="mt-8 h-[220px] w-full rounded-3xl object-cover sm:h-[300px] lg:h-[380px]">
</section>
@endblock

@block([
    'key' => 'resource_cards',
    'name' => 'Resource Cards',
    'group' => 'Home',
    'version' => 1,
    'fields' => [
        ['id' => 'heading', 'type' => 'text'],
        ['id' => 'subheading', 'type' => 'text'],
        ['id' => 'cards', 'type' => 'repeater', 'fields' => [
            ['id' => 'accent', 'type' => 'select', 'options' => ['teal'=>'Teal','yellow'=>'Yellow','orange'=>'Orange','purple'=>'Purple','green'=>'Green','blue'=>'Blue','pink'=>'Pink']],
            ['id' => 'image', 'type' => 'media', 'kind' => 'image'],
            ['id' => 'label', 'type' => 'text'],
        ]],
    ],
])

@php
    $cards = $s->get('cards', []);
    $accent = fn ($k) => match ($k) {
        'teal' => 'bg-wodi-teal', 'yellow' => 'bg-wodi-yellow', 'orange' => 'bg-wodi-orange',
        'purple' => 'bg-wodi-purple', 'green' => 'bg-wodi-green', 'blue' => 'bg-wodi-blue',
        'pink' => 'bg-wodi-pink', default => 'bg-wodi-teal',
    };
@endphp

<section class="mx-auto max-w-[1400px] px-5 pb-20 lg:px-8">
    <div class="flex items-end justify-between gap-6">
        <div>
            <h2 class="text-2xl font-bold lg:text-[28px]">{{ $s->get('heading') }}</h2>
            <p class="mt-1 text-sm text-wodi-muted">{{ $s->get('subheading') }}</p>
        </div>
        <div class="flex shrink-0 gap-2">
            <button type="button" aria-label="Previous" class="grid size-9 place-items-center rounded-full bg-wodi-pink text-white hover:bg-wodi-pink-dark">
                <x-lucide-arrow-left class="size-4" />
            </button>
            <button type="button" aria-label="Next" class="grid size-9 place-items-center rounded-full bg-wodi-pink text-white hover:bg-wodi-pink-dark">
                <x-lucide-arrow-right class="size-4" />
            </button>
        </div>
    </div>

    <div class="no-scrollbar mt-6 flex snap-x snap-mandatory gap-5 overflow-x-auto pb-2">
        @foreach ($cards as $card)
            <article class="{{ $accent(data_get($card, 'accent')) }} flex w-[260px] shrink-0 snap-start flex-col gap-4 rounded-[26px] p-3">
                @php $img = data_get($card, 'image'); $url = $img ? \App\Models\Media::find($img)?->url() : null; @endphp
                <div class="h-[190px] overflow-hidden rounded-[20px] bg-white">
                    @if ($url)<img src="{{ $url }}" alt="" class="h-full w-full object-cover">@endif
                </div>
                <button type="button" class="mx-auto inline-flex items-center gap-1.5 rounded-full bg-white px-4 py-1.5 text-[11px] font-medium text-wodi-ink shadow-sm">
                    {{ data_get($card, 'label', 'download') }}
                    <x-lucide-download class="size-3.5" />
                </button>
            </article>
        @endforeach
    </div>
</section>
@endblock

@block([
    'key' => 'classroom_split',
    'name' => 'Classroom Split',
    'group' => 'Home',
    'version' => 1,
    'fields' => [
        ['id' => 'heading', 'type' => 'text'],
        ['id' => 'points', 'type' => 'repeater', 'fields' => [
            ['id' => 'text', 'type' => 'text'],
        ]],
        ['id' => 'image', 'type' => 'media', 'kind' => 'image'],
    ],
])

@php
    $image = $s->mediaUrl('image') ?? '/images/home/kid-with-book.png';
    $points = $s->get('points', []);
@endphp

<section class="relative overflow-hidden bg-white py-16 lg:py-20">
    <img src="/images/patterns/grid.png" alt="" class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-50">
    <img src="/images/doodles/heart.png" alt="" class="pointer-events-none absolute top-10 left-6 hidden w-9 lg:block">
    <img src="/images/doodles/school.png" alt="" class="pointer-events-none absolute bottom-16 left-[8%] hidden w-9 lg:block">
    <img src="/images/doodles/school-bus.png" alt="" class="pointer-events-none absolute bottom-8 left-[32%] hidden w-10 lg:block">

    <div class="relative mx-auto grid max-w-[1100px] items-center gap-10 px-5 lg:grid-cols-2 lg:px-8">
        <div>
            <h2 class="max-w-md text-3xl leading-snug font-extrabold lg:text-[34px]">{{ $s->get('heading') }}</h2>
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
            <span class="absolute top-1/2 left-1/2 aspect-square w-[300px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-wodi-blue sm:w-[340px]"></span>
            <img src="{{ $image }}" alt="" class="relative w-[260px] object-contain sm:w-[300px]">
            <span class="absolute top-4 right-6 grid size-9 place-items-center rounded-full bg-white shadow-md">
                <x-lucide-heart class="size-4 fill-wodi-pink text-wodi-pink" />
            </span>
        </div>
    </div>
</section>
@endblock

@block([
    'key' => 'steps',
    'name' => 'Steps / Process',
    'group' => 'Home',
    'version' => 1,
    'fields' => [
        ['id' => 'heading', 'type' => 'text'],
        ['id' => 'subheading', 'type' => 'text'],
        ['id' => 'steps', 'type' => 'repeater', 'fields' => [
            ['id' => 'icon', 'type' => 'select', 'options' => ['user'=>'User','edit'=>'Edit','id'=>'ID card','check'=>'Check','star'=>'Star','calendar'=>'Calendar']],
            ['id' => 'title', 'type' => 'text'],
            ['id' => 'body', 'type' => 'textarea'],
        ]],
    ],
])

@php
    $steps = $s->get('steps', []);
    $icon = fn ($k) => match ($k) {
        'user' => 'lucide-user-round', 'edit' => 'lucide-square-pen', 'id' => 'lucide-id-card',
        'check' => 'lucide-check', 'star' => 'lucide-star', 'calendar' => 'lucide-calendar-days',
        default => 'lucide-user-round',
    };
    $count = count($steps);
@endphp

<section class="bg-white py-16 lg:py-20">
    <div class="mx-auto max-w-[1400px] px-5 lg:px-8">
        <h2 class="text-3xl font-bold lg:text-[38px]">{{ $s->get('heading') }}</h2>
        <p class="mt-2 text-[15px] text-wodi-muted">{{ $s->get('subheading') }}</p>

        <div class="mt-14 grid gap-12 lg:grid-cols-3 lg:gap-6">
            @foreach ($steps as $index => $step)
                <div class="relative flex flex-col items-center text-center">
                    @if ($index < $count - 1)
                        <span class="absolute top-12 left-[calc(50%+3.5rem)] right-[calc(-50%+3.5rem)] hidden h-px bg-wodi-pink/25 lg:block"></span>
                    @endif
                    <div class="relative">
                        <span class="grid size-24 place-items-center rounded-full bg-wodi-pink/10">
                            <x-dynamic-component :component="$icon(data_get($step, 'icon'))" class="size-9 text-wodi-pink" />
                        </span>
                        <span class="absolute -top-1 -left-1 grid size-7 place-items-center rounded-full bg-wodi-pink text-xs font-bold text-white">{{ $index + 1 }}</span>
                    </div>
                    <h3 class="mt-6 text-lg font-bold">{{ data_get($step, 'title') }}</h3>
                    <p class="mt-3 max-w-xs text-sm leading-relaxed text-wodi-muted">{{ data_get($step, 'body') }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endblock

@block([
    'key' => 'banner_split',
    'name' => 'Banner Split',
    'group' => 'Home',
    'version' => 1,
    'fields' => [
        ['id' => 'image', 'type' => 'media', 'kind' => 'image'],
        ['id' => 'heading', 'type' => 'text'],
        ['id' => 'body', 'type' => 'textarea'],
    ],
])

@php
    $image = $s->mediaUrl('image') ?? '/images/home/kids-smile.png';
@endphp

<section class="py-16 lg:py-20">
    <div class="mx-auto max-w-[1200px] px-5 lg:px-8">
        <div class="grid overflow-hidden rounded-[32px] md:grid-cols-2">
            <img src="{{ $image }}" alt="" class="h-64 w-full object-cover md:h-full">
            <div class="flex flex-col justify-center bg-wodi-pink px-8 py-10 text-white lg:px-12">
                <h2 class="text-2xl leading-snug font-extrabold lg:text-[32px]">{{ $s->get('heading') }}</h2>
                <p class="mt-5 text-sm leading-relaxed text-white/90">{{ $s->get('body') }}</p>
            </div>
        </div>
    </div>
</section>
@endblock

@block([
    'key' => 'testimonials',
    'name' => 'Testimonials',
    'group' => 'Home',
    'version' => 1,
    'fields' => [
        ['id' => 'heading', 'type' => 'text'],
        ['id' => 'image', 'type' => 'media', 'kind' => 'image'],
        ['id' => 'quote', 'type' => 'textarea'],
        ['id' => 'avatar', 'type' => 'media', 'kind' => 'image'],
        ['id' => 'name', 'type' => 'text'],
        ['id' => 'role', 'type' => 'text'],
    ],
])

@php
    $image = $s->mediaUrl('image') ?? '/images/home/testimonial-photo.png';
    $avatar = $s->mediaUrl('avatar');
@endphp

<section class="relative overflow-hidden bg-white py-16 lg:py-20">
    <img src="/images/patterns/grid.png" alt="" class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-50">

    <div class="relative mx-auto max-w-[1200px] px-5 lg:px-8">
        <h2 class="text-center text-3xl font-bold lg:text-[34px]">{!! $s->get('heading') !!}</h2>

        <div class="mt-12 flex items-center gap-6">
            <button type="button" aria-label="Previous testimonial" class="hidden size-10 shrink-0 place-items-center rounded-full border border-wodi-pink text-wodi-pink hover:bg-wodi-pink hover:text-white lg:grid">
                <x-lucide-arrow-left class="size-4" />
            </button>

            <figure class="grid flex-1 items-center gap-8 md:grid-cols-2">
                <img src="{{ $image }}" alt="" class="h-[280px] w-full rounded-3xl object-cover">
                <div>
                    <blockquote class="text-lg leading-relaxed text-wodi-ink lg:text-xl">{{ $s->get('quote') }}</blockquote>
                    <figcaption class="mt-7 flex items-center gap-3">
                        @if ($avatar)
                            <img src="{{ $avatar }}" alt="" class="size-11 rounded-full object-cover">
                        @else
                            <span class="grid size-11 place-items-center rounded-full bg-wodi-cream">
                                <x-lucide-user class="size-5 text-wodi-pink/50" />
                            </span>
                        @endif
                        <div>
                            <p class="font-bold">{{ $s->get('name') }}</p>
                            <p class="text-sm text-wodi-muted">{{ $s->get('role') }}</p>
                        </div>
                    </figcaption>
                </div>
            </figure>

            <button type="button" aria-label="Next testimonial" class="hidden size-10 shrink-0 place-items-center rounded-full border border-wodi-pink text-wodi-pink hover:bg-wodi-pink hover:text-white lg:grid">
                <x-lucide-arrow-right class="size-4" />
            </button>
        </div>
    </div>
</section>
@endblock

@block([
    'key' => 'newsletter',
    'name' => 'Newsletter',
    'group' => 'Home',
    'version' => 1,
    'fields' => [
        ['id' => 'heading', 'type' => 'text'],
    ],
])

@include('partials.newsletter')
@endblock

@block([
    'key' => 'hero',
    'name' => 'Hero',
    'group' => 'Headers',
    'version' => 1,
    'fields' => [
        ['id' => 'eyebrow', 'type' => 'text'],
        ['id' => 'title', 'type' => 'text', 'required' => true],
        ['id' => 'body', 'type' => 'richtext'],
        ['id' => 'image', 'type' => 'media', 'kind' => 'image'],
        ['id' => 'align', 'type' => 'select', 'options' => ['left'=>'Left','center'=>'Center'], 'default' => 'center'],
    ],
])

{{--
 | Hero section template. Presentational values (radius, spacing) live here, not
 | in the DB. `$s` is a SectionData: $s->get('field'), $s->mediaUrl('image').
 | The align choice maps through match() so Tailwind sees literal classes.
--}}
@php
    $align = match ($s->get('align', 'center')) {
        'left' => 'text-left items-start',
        default => 'text-center items-center',
    };
@endphp

<section class="bg-wodi-blush px-4 py-16 lg:py-24">
    <div class="mx-auto flex max-w-3xl flex-col gap-5 {{ $align }}">
        @if ($s->get('eyebrow'))
            <p class="text-sm font-semibold tracking-wide text-wodi-pink uppercase">
                {{ $s->get('eyebrow') }}
            </p>
        @endif

        <h1 class="font-heading text-3xl font-extrabold text-wodi-ink lg:text-5xl">
            {{ $s->get('title') }}
        </h1>

        @if ($s->get('body'))
            <div class="prose max-w-none text-wodi-muted">
                {!! $s->get('body') !!}
            </div>
        @endif

        @if ($s->mediaUrl('image'))
            <img src="{{ $s->mediaUrl('image') }}" alt="{{ $s->mediaAlt('image') }}"
                 class="mt-4 w-full rounded-[4px] object-cover">
        @endif
    </div>
</section>
@endblock

