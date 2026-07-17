@extends('layouts.site')

@section('title', $page->meta_title ?: $page->title . ' — ' . config('app.name'))
@section('meta_description', $page->meta_description ?? '')

@section('content')
    @foreach ($blocks as $block)
        @if ($editor ?? false)
            <div data-cms-block="{{ $block->id }}">
        @endif

        @switch ($block->type)
            {{-- three colour cards, each with a cutout breaking out the top --}}
            @case('resources_hero')
                @php
                    $src = function ($row, string $key, string $fallback): ?string {
                        $id = data_get($row, $key);

                        return ($id ? \App\Models\Media::find($id)?->url() : null) ?? data_get($row, $fallback);
                    };

                    /*
                     | Each cutout's subject runs to the BOTTOM of its PNG canvas, so anchoring the
                     | image at bottom-0 lands the subject's feet exactly on the card's bottom edge;
                     | the head/arms then break out of the top. Element widths are derived from the
                     | real alpha bboxes (heavy transparent padding, so they're far wider than the
                     | subject's visual width):
                     |   kids-cheer  subject 95.8% of canvas, centre -0.42%  → w 83%
                     |   boy-thumbs  subject 47.3% of canvas, centre -1.52%  → w 191%
                     |   girl-wave   subject 63.7% of canvas, centre +6.88%  → w 142%
                     | translateX = -50% minus the subject's centre offset, so the SUBJECT is centred.
                     |
                     | The geometry belongs to the specific PNG, not to the content — it is keyed
                     | off the cutout and stays here so Tailwind can see the literal classes.
                     */
                    $geometry = fn($v) => match ($v) {
                        'cheer' => ['bg-[#F5344E]', 'aspect-[16/9]', 'w-[83%]', '-translate-x-[49.6%]'],
                        // centre card is taller, per Figma
                        'thumbs' => ['bg-[#22C55E]', 'aspect-square', 'w-[191%]', '-translate-x-[48.5%]'],
                        'wave' => ['bg-wodi-yellow', 'aspect-[16/9]', 'w-[142%]', '-translate-x-[56.9%]'],
                        default => ['bg-wodi-yellow', 'aspect-[16/9]', 'w-[83%]', '-translate-x-1/2'],
                    };
                @endphp

                <section class="relative overflow-hidden bg-wodi-blush">
                    <img src="/images/patterns/grid.png" alt=""
                         class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-60">

                    <div class="relative mx-auto max-w-[1300px] px-4 pt-24 pb-16 lg:px-8 lg:pt-28">
                        <h1 class="font-heading mx-auto max-w-2xl text-center text-3xl leading-tight font-extrabold text-wodi-pink sm:text-4xl lg:text-[42px]">
                            {{ $block->get('title') }}
                        </h1>

                        <p class="mx-auto mt-5 max-w-md text-center text-sm leading-relaxed text-wodi-pink lg:text-[15px]">
                            {{ $block->get('subtitle') }}
                        </p>

                        {{-- three cards — bottom-aligned, centre one taller --}}
                        <div class="mt-36 grid grid-cols-1 items-end gap-x-10 gap-y-36 sm:grid-cols-3 lg:mt-44 lg:gap-x-14">
                            @foreach ($block->get('cards', []) as $card)
                                @php [$bg, $ratio, $w, $shiftX] = $geometry(data_get($card, 'variant')); @endphp

                                <div class="relative">
                                    <div class="{{ $bg }} {{ $ratio }} w-full rounded-2xl"></div>

                                    <img src="{{ $src($card, 'image', 'src') }}" alt="{{ data_get($card, 'alt') }}"
                                         class="pointer-events-none absolute bottom-0 left-1/2 {{ $w }} {{ $shiftX }} max-w-none object-contain">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @break

            {{-- searchable, filterable, paginated materials library --}}
            @case('resources_programs')
                @php
                    // Absent in the editor preview (only the public route loads them).
                    $materials = $materials ?? collect();
                    $categories = $categories ?? collect();
                    $activeCategory = $activeCategory ?? '';
                    $q = $q ?? '';
                @endphp

                <section id="programs" class="bg-wodi-blush pb-20">
                    <div class="mx-auto max-w-[1400px] px-4 lg:px-8">
                        <h2 class="text-center text-3xl font-bold lg:text-[38px]">{{ $block->get('heading') }}</h2>

                        <p class="mx-auto mt-3 max-w-lg text-center text-sm leading-relaxed text-wodi-muted">
                            {{ $block->get('subheading') }}
                        </p>

                        {{-- search: a real GET to this page --}}
                        <form action="{{ route('resources') }}" method="GET" class="mx-auto mt-8 max-w-2xl">
                            @if ($activeCategory !== '')
                                <input type="hidden" name="category" value="{{ $activeCategory }}">
                            @endif

                            <div class="flex items-center gap-2 rounded-full bg-white p-1.5 pl-6 shadow-sm">
                                <label for="resource-search" class="sr-only">Search resources</label>

                                <input id="resource-search" name="q" type="search" value="{{ $q }}"
                                       placeholder="{{ $block->get('search_placeholder') }}"
                                       class="min-w-0 flex-1 bg-transparent py-2.5 text-sm text-wodi-ink placeholder:text-wodi-muted/70 focus:outline-none">

                                <button type="submit"
                                        class="shrink-0 rounded-full bg-wodi-pink px-8 py-2.5 text-xs font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                                    {{ $block->get('search_label', 'Search') }}
                                </button>
                            </div>
                        </form>

                        {{-- category tabs: real server-side filter links, keeping the search term --}}
                        <div class="no-scrollbar mx-auto mt-6 flex max-w-4xl justify-start gap-2 overflow-x-auto pb-1 sm:justify-center">
                            <a href="{{ route('resources', array_filter(['q' => $q])) }}" @class([
                                'shrink-0 rounded-full px-4 py-1.5 text-[11px] font-medium whitespace-nowrap transition-colors',
                                'bg-wodi-pink text-white' => $activeCategory === '',
                                'bg-transparent text-wodi-ink hover:bg-white' => $activeCategory !== '',
                            ])>
                                All
                            </a>

                            @foreach ($categories as $cat)
                                <a href="{{ route('resources', array_filter(['category' => $cat->slug, 'q' => $q])) }}" @class([
                                    'shrink-0 rounded-full px-4 py-1.5 text-[11px] font-medium whitespace-nowrap transition-colors',
                                    'bg-wodi-pink text-white' => $activeCategory === $cat->slug,
                                    'bg-transparent text-wodi-ink hover:bg-white' => $activeCategory !== $cat->slug,
                                ])>
                                    {{ $cat->name }}
                                </a>
                            @endforeach
                        </div>

                        {{-- cards --}}
                        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            @forelse ($materials as $material)
                                <article class="flex flex-col rounded-2xl bg-white p-3 shadow-sm">
                                    @if ($material->image_path)
                                        <img src="{{ $material->image_path }}" alt="{{ $material->title }}"
                                             loading="lazy"
                                             class="aspect-[4/3] w-full rounded-xl object-cover">
                                    @endif

                                    <p class="flex-1 px-2 py-4 text-center text-xs leading-relaxed text-wodi-ink">
                                        {{ $material->title }}
                                    </p>

                                    <a href="{{ $material->url ?? '#' }}"
                                       class="mx-auto mb-1 inline-flex items-center gap-1.5 rounded-full border border-wodi-pink px-6 py-2 text-[11px] font-medium text-wodi-pink transition-colors hover:bg-wodi-pink hover:text-white">
                                        {{ $material->ctaLabel() }}

                                        @if ($material->ctaIcon())
                                            <x-dynamic-component :component="$material->ctaIcon()" class="size-3.5" />
                                        @endif
                                    </a>
                                </article>
                            @empty
                                <p class="col-span-full py-16 text-center text-sm text-wodi-muted">
                                    No resources found. Try a different search or category.
                                </p>
                            @endforelse
                        </div>

                        @if ($materials instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $materials->hasPages())
                            <div class="mt-10 flex items-center justify-center gap-3 text-sm">
                                @if ($materials->onFirstPage())
                                    <span class="rounded-full px-4 py-2 text-wodi-muted/50">Previous</span>
                                @else
                                    <a href="{{ $materials->previousPageUrl() }}"
                                       class="rounded-full border border-wodi-pink px-4 py-2 text-wodi-pink transition-colors hover:bg-wodi-pink hover:text-white">Previous</a>
                                @endif

                                <span class="text-wodi-muted">Page {{ $materials->currentPage() }} of {{ $materials->lastPage() }}</span>

                                @if ($materials->hasMorePages())
                                    <a href="{{ $materials->nextPageUrl() }}"
                                       class="rounded-full border border-wodi-pink px-4 py-2 text-wodi-pink transition-colors hover:bg-wodi-pink hover:text-white">Next</a>
                                @else
                                    <span class="rounded-full px-4 py-2 text-wodi-muted/50">Next</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </section>
            @break
        @endswitch

        @if ($editor ?? false)
            </div>
        @endif
    @endforeach
@endsection
