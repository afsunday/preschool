@extends('layouts.site')

@section('title', $page->meta_title ?: $page->title . ' — ' . config('app.name'))
@section('meta_description', $page->meta_description ?? '')

@section('content')
    @foreach ($blocks as $block)
        @if ($editor ?? false)
            <div data-cms-block="{{ $block->id }}">
        @endif

        @switch ($block->type)
            @case('faq_hero')
                <section class="relative overflow-hidden bg-wodi-blush">
                    <img src="/images/patterns/grid.png" alt=""
                         class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-60">

                    <div class="relative mx-auto max-w-[1400px] px-4 pt-24 pb-10 lg:px-8 lg:pt-28">
                        <h1 class="font-heading mx-auto max-w-xl text-center text-3xl leading-tight font-extrabold text-wodi-pink sm:text-4xl lg:text-[46px]">
                            {{ $block->get('title') }}
                        </h1>
                    </div>
                </section>
            @break

            {{-- two balanced columns (masonry-style, keeps reading order) --}}
            @case('faq_list')
                <section class="bg-wodi-blush pb-20">
                    <div class="mx-auto max-w-[1250px] px-4 lg:px-8">
                        <div class="grid gap-5 md:grid-cols-2">
                            @foreach ($block->get('faqs', []) as $faq)
                                <article class="flex gap-4 rounded-2xl bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-wodi-pink/10">
                                        <x-lucide-file-question class="size-4 text-wodi-pink" />
                                    </span>

                                    <div>
                                        <h2 class="text-sm font-bold text-wodi-ink">{{ data_get($faq, 'question') }}</h2>
                                        <p class="mt-2 text-xs leading-relaxed text-wodi-muted">{{ data_get($faq, 'answer') }}</p>
                                    </div>
                                </article>
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
