@php
    $links = $block->get('links', []);
    $logo = $block->mediaUrl('logo') ?? '/images/brand/logo.png';

    // The primary CTA (enrolment) points to OneList, an external site. Open it in
    // a new tab so families don't lose the WODI page they came from.
    $primaryUrl = $block->get('primary_url', '#');
    $primaryExternal = \Illuminate\Support\Str::startsWith($primaryUrl, ['http://', 'https://']);

    // Active state used to key off route names. A CMS stores a URL, not a route
    // name — a name is an implementation detail that would break the menu the
    // day someone renames a route. Match on the path instead; anchors (#…) and
    // external links never match.
    $isActive = function (?string $url): bool {
        $path = trim((string) parse_url((string) $url, PHP_URL_PATH), '/');

        if (! $url || str_starts_with($url, '#') || str_starts_with($url, 'http')) {
            return false;
        }

        return request()->is($path === '' ? '/' : $path);
    };
@endphp

        {{--
        | Fixed nav. Transparent at the top (so the hero pattern flows behind it —
        | this is what removes the seam/"bottom bar"), then frosted glass on scroll.
        --}}
        <header x-data="{ open: false, scrolled: false }" @scroll.window.passive="scrolled = window.scrollY > 10"
            class="fixed inset-x-0 top-0 z-50 border-b border-wodi-pink/20 transition-colors duration-300" :class="scrolled || open
                    ? 'bg-wodi-cream/70 backdrop-blur-xl backdrop-saturate-150'
                    : 'bg-transparent'">

            {{-- h-14 (56px) matches Figma; h-20 was pushing every page's content down 24px --}}
            <div class="mx-auto flex h-14 max-w-[1400px] items-center gap-8 px-5 lg:px-8">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="shrink-0">
                    <img src="{{ $logo }}" alt="{{ config('app.name') }}" class="h-9 w-auto">
                </a>

                {{-- Desktop nav.
                | Links are FULL HEIGHT (h-full) so the active underline can sit at
                | bottom-0 and meet the header's bottom border, rather than floating
                | a few px above it. --}}
                <nav class="hidden h-full flex-1 items-center gap-7 lg:flex">
                    @foreach ($links as $item)
                        @php $active = $isActive(data_get($item, 'url')); @endphp

                        <a href="{{ data_get($item, 'url', '#') }}" @class([
                            'relative flex h-full items-center whitespace-nowrap text-[13px] font-medium transition-colors',
                            'text-wodi-pink' => $active,
                            'text-wodi-ink hover:text-wodi-pink' => !$active,
                        ])>
                            {{ data_get($item, 'label') }}

                            @if ($active)
                                <span class="absolute bottom-0 left-0 h-0.5 w-full bg-wodi-pink"></span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                {{-- Desktop CTAs --}}
                <div class="ml-auto hidden shrink-0 items-center gap-3 lg:flex">
                    <a href="{{ $primaryUrl }}" @if ($primaryExternal) target="_blank" rel="noopener noreferrer" @endif
                        class="rounded-full bg-wodi-pink px-4 py-2.5 text-[11px] font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                        {{ $block->get('primary_label') }}
                    </a>

                    <a href="{{ $block->get('secondary_url', '#') }}"
                        class="rounded-full border border-wodi-pink bg-white/40 px-4 py-2.5 text-[11px] font-medium text-wodi-pink transition-colors hover:bg-wodi-pink hover:text-white">
                {{ $block->get('secondary_label') }}
            </a>
        </div>

        {{-- Mobile toggle --}}
        <button type="button"
                @click="open = !open"
                class="ml-auto inline-flex items-center justify-center rounded-lg p-2 text-wodi-ink lg:hidden"
                aria-label="Toggle navigation">
            <x-lucide-menu x-show="!open" class="size-6" />
            <x-lucide-x x-show="open" x-cloak class="size-6" />
        </button>
    </div>

    {{-- Mobile menu --}}
    <div x-show="open" x-cloak x-transition.opacity class="lg:hidden">
        <nav class="mx-auto flex max-w-[1400px] flex-col gap-1 px-5 pb-5">
            @foreach ($links as $item)
                <a href="{{ data_get($item, 'url', '#') }}"
                   @click="open = false"
                   class="rounded-lg px-3 py-2.5 text-[15px] font-medium text-wodi-ink hover:bg-wodi-pink/5 hover:text-wodi-pink">
                    {{ data_get($item, 'label') }}
                </a>
            @endforeach

            <div class="mt-3 flex flex-col gap-2">
                <a href="{{ $primaryUrl }}" @if ($primaryExternal) target="_blank" rel="noopener noreferrer" @endif @click="open = false"
                   class="rounded-full bg-wodi-pink px-6 py-3 text-center text-sm font-medium text-white">
                    {{ $block->get('primary_label') }}
                </a>

                <a href="{{ $block->get('secondary_url', '#') }}" @click="open = false"
                   class="rounded-full border border-wodi-pink px-6 py-3 text-center text-sm font-medium text-wodi-pink">
                    {{ $block->get('secondary_label') }}
                </a>
            </div>
        </nav>
    </div>
</header>
