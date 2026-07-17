@extends('layouts.site')

@section('title', $page->meta_title ?: $page->title . ' — ' . config('app.name'))
@section('meta_description', $page->meta_description ?? '')

@section('content')
    @foreach ($blocks as $block)
        @if ($editor ?? false)
            <div data-cms-block="{{ $block->id }}">
        @endif

        @switch ($block->type)
            {{-- title + a clean responsive strip of photos --}}
            @case('gallery_hero')
                @php
                    $src = function ($row, string $key, string $fallback): ?string {
                        $id = data_get($row, $key);

                        return ($id ? \App\Models\Media::find($id)?->url() : null) ?? data_get($row, $fallback);
                    };

                    $photos = array_values(array_filter(array_map(
                        fn ($photo) => $src($photo, 'image', 'src'),
                        $block->get('photos', []),
                    )));
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

                    {{-- A plain responsive row of photos: two across on phones, up
                       | to six on desktop. No tilt, no perspective — just a grid. --}}
                    <div class="relative mx-auto max-w-[1400px] px-4 pb-12 lg:px-8">
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 lg:gap-5">
                            @foreach ($photos as $img)
                                <img src="{{ $img }}" alt=""
                                     loading="lazy"
                                     class="aspect-[4/5] w-full rounded-lg object-cover shadow-md">
                            @endforeach
                        </div>
                    </div>
                </section>
            @break

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
        @endswitch

        @if ($editor ?? false)
            </div>
        @endif
    @endforeach
@endsection
