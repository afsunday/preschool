@php
    $columns = $block->get('columns', []);
    $legal = $block->get('legal_links', []);
    $logo = $block->mediaUrl('logo') ?? '/images/brand/logo.png';
@endphp

<footer class="relative overflow-hidden bg-wodi-cream pt-16">
    {{-- watermark --}}
    <span aria-hidden="true"
          class="pointer-events-none absolute inset-x-0 bottom-0 hidden text-center text-[9rem] leading-none font-extrabold tracking-tight text-white/50 select-none lg:block">
        {{ $block->get('watermark') }}
    </span>

    <div class="relative mx-auto max-w-[1400px] px-5 lg:px-8">
        {{-- The column count is baked into the grid: the logo plus three link
             columns is the Figma layout, not something the content decides. --}}
        <div class="grid gap-10 lg:grid-cols-[1fr_repeat(3,minmax(0,1fr))]">
            <a href="{{ route('home') }}" class="shrink-0">
                <img src="{{ $logo }}" alt="{{ config('app.name') }}" class="h-12 w-auto">
            </a>

            @foreach ($columns as $column)
                <div>
                    <h3 class="text-lg font-bold">{{ data_get($column, 'heading') }}</h3>

                    <ul class="mt-5 space-y-3">
                        @foreach (data_get($column, 'links', []) as $link)
                            <li>
                                <a href="{{ data_get($link, 'url', '#') }}"
                                   class="text-[15px] text-wodi-ink transition-colors hover:text-wodi-pink">
                                    {{ data_get($link, 'label') }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <hr class="mt-12 border-wodi-ink/10">

        <p class="mt-8 max-w-5xl text-sm leading-relaxed text-wodi-ink/80">
            {{ $block->get('about_text') }}
        </p>

        <div class="mt-10 flex flex-col gap-3 pb-10 text-sm sm:flex-row sm:items-center sm:justify-between">
            {{-- The year and app name are computed, not content — nobody should
                 have to edit the copyright every January. --}}
            <p class="font-medium">
                &copy; Copyright {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>

            <div class="flex items-center gap-2">
                @foreach ($legal as $i => $link)
                    @if ($i > 0)
                        <span class="text-wodi-ink/30">|</span>
                    @endif

                    <a href="{{ data_get($link, 'url', '#') }}" class="hover:text-wodi-pink">
                        {{ data_get($link, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</footer>
