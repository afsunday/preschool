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
            {{-- title + full-bleed strip of tilted photos --}}
            @case('gallery_hero')
                @php
                    $src = function ($row, string $key, string $fallback): ?string {
                        $id = data_get($row, $key);

                        return ($id ? \App\Models\Media::find($id)?->url() : null) ?? data_get($row, $fallback);
                    };

                    /**
                     | PANORAMA — the same cylinder maths the uiinitiative Panorama Slider uses,
                     | applied STATICALLY (no slider, no JS, nothing draggable).
                     |
                     | Every photo sits on the surface of a cylinder. For a photo at position
                     | `p` along the band (0 = centre, ±2.5 = the ends):
                     |
                     |   S          = 1 - cos(p * (rotate/180) * PI)      // curvature falloff
                     |   radius     = slide * 0.5 / sin(rotate / 2)       // chord -> radius
                     |   translateX = p * (slide / 3) * S                 // draw in toward centre
                     |   translateZ = radius * S - depth                  // seat it on the cylinder
                     |   rotateY    = p * rotate                          // turn it to face centre
                     |
                     | This is why hand-tuned rotate/scale never worked. The formula puts the
                     | OUTER photos FORWARD (z ~ -39) and the INNER ones BACK (z ~ -194), so the
                     | ends render ~12% larger, while rotateY makes each one a trapezoid. The
                     | inward pinch on the top AND bottom edges falls out of the geometry — it
                     | isn't something you dial in by eye.
                     */
                    // Gentle curve: a large `rotate` foreshortens the end photos so hard that
                    // they no longer fill their grid cells and the band gains huge gaps.
                    $rotate = 6;     // degrees between adjacent photos
                    $depth  = 80;    // how far the whole band is pushed back
                    $slide  = 300;   // nominal photo width (px) used to size the cylinder

                    $radius = $slide * 0.5 / sin(deg2rad($rotate) / 2);

                    $photos = array_values(array_filter(array_map(
                        fn ($photo) => $src($photo, 'image', 'src'),
                        $block->get('photos', []),
                    )));

                    $count = count($photos);
                    $strip = [];

                    foreach ($photos as $i => $img) {
                        // centre the band on 0: for 6 photos → -2.5 .. +2.5
                        $p = $i - ($count - 1) / 2;

                        $falloff = 1 - cos($p * ($rotate / 180) * M_PI);

                        $translateX = $p * ($slide / 3) * $falloff;
                        $translateZ = $radius * $falloff - $depth;
                        $angle      = $p * $rotate;

                        $strip[] = [
                            'img'       => $img,
                            'transform' => sprintf(
                                'translateX(%.1fpx) translateZ(%.1fpx) rotateY(%.1fdeg)',
                                $translateX, $translateZ, $angle
                            ),
                        ];
                    }
                @endphp

                <section class="relative overflow-hidden bg-wodi-blush">
                    <img src="/images/patterns/grid.png" alt=""
                         class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-60">

                    <div class="relative mx-auto max-w-[1400px] px-4 pt-24 pb-12 text-center lg:px-8 lg:pt-28">
                        <h1 class="font-heading mx-auto max-w-lg text-3xl leading-tight font-extrabold text-wodi-pink sm:text-4xl lg:text-[44px]">
                            {{ $block->get('title') }}
                        </h1>

                        <p class="mx-auto mt-5 max-w-lg text-sm leading-relaxed text-wodi-pink lg:text-[15px]">
                            {{ $block->get('subtitle') }}
                        </p>
                    </div>

                    {{-- perspective lives on the CONTAINER so every photo shares one vanishing
                       | point and they read as a single curved band. preserve-3d keeps the
                       | children in that 3D space instead of flattening them.
                       | grid-cols-6 is the Figma band width — the maths self-centres, but the
                       | band is drawn for six photos. --}}
                    <div class="relative w-full overflow-hidden pb-6">
                        <div class="grid grid-cols-6 items-center gap-4
                                    [perspective:1200px] [transform-style:preserve-3d] lg:gap-5">
                            @foreach ($strip as $photo)
                                <img src="{{ $photo['img'] }}" alt=""
                                     loading="lazy"
                                     style="transform: {{ $photo['transform'] }}"
                                     class="aspect-[4/5] w-full object-cover shadow-lg">
                            @endforeach
                        </div>
                    </div>
                </section>
            @break

            {{-- Six groups, three per row. Each group is a 2-column block:
               |   • left  — one TALL photo spanning both rows
               |   • right — two stacked photos
               |
               | The group gets an explicit HEIGHT (not aspect-ratio) so `grid-rows-2`
               | resolves reliably and the row-span actually spans.
               | Container is ~1780px wide in Figma — near full-bleed, not a narrow column. --}}
            @case('gallery_grid')
                @php
                    $src = function ($row, string $key, string $fallback): ?string {
                        $id = data_get($row, $key);

                        return ($id ? \App\Models\Media::find($id)?->url() : null) ?? data_get($row, $fallback);
                    };
                @endphp

                <section class="bg-wodi-blush py-16 lg:py-24">
                    <div class="mx-auto max-w-[1800px] px-4 lg:px-8">
                        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 lg:gap-12">
                            @foreach ($block->get('groups', []) as $group)
                                <div class="grid h-[300px] grid-cols-2 grid-rows-2 gap-3 lg:h-[460px]">
                                    <img src="{{ $src($group, 'tall', 'tall_src') }}" alt=""
                                         loading="lazy"
                                         class="row-span-2 h-full w-full object-cover">

                                    <img src="{{ $src($group, 'top', 'top_src') }}" alt=""
                                         loading="lazy"
                                         class="h-full w-full object-cover">

                                    <img src="{{ $src($group, 'bottom', 'bottom_src') }}" alt=""
                                         loading="lazy"
                                         class="h-full w-full object-cover">
                                </div>
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
