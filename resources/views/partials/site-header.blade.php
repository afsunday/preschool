@php
    $navItems = [
        ['label' => 'Home', 'url' => route('home'), 'route' => 'home'],
        ['label' => 'About us', 'url' => route('about'), 'route' => 'about'],
        ['label' => 'Admissions', 'url' => route('admissions'), 'route' => 'admissions'],
        ['label' => 'Parents & Kids Resource', 'url' => route('resources'), 'route' => 'resources'],
        ['label' => 'Forms & Policies', 'url' => route('forms'), 'route' => 'forms'],
        ['label' => 'Gallery', 'url' => route('gallery'), 'route' => 'gallery'],
        ['label' => 'FAQ', 'url' => route('faq'), 'route' => 'faq'],
        ['label' => 'Contact Us', 'url' => route('contact'), 'route' => 'contact'],
    ];
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
                    <img src="/images/brand/logo.png" alt="{{ config('app.name') }}" class="h-9 w-auto">
                </a>
        
                {{-- Desktop nav.
                | Links are FULL HEIGHT (h-full) so the active underline can sit at
                | bottom-0 and meet the header's bottom border, rather than floating
                | a few px above it. --}}
                <nav class="hidden h-full flex-1 items-center gap-7 lg:flex">
                    @foreach ($navItems as $item)
                        @php $isActive = isset($item['route']) && request()->routeIs($item['route']); @endphp

                        <a href="{{ $item['url'] }}" @class([
                            'relative flex h-full items-center whitespace-nowrap text-[13px] font-medium transition-colors',
                            'text-wodi-pink' => $isActive,
                            'text-wodi-ink hover:text-wodi-pink' => !$isActive,
                        ])>
                            {{ $item['label'] }}

                            @if ($isActive)
                                <span class="absolute bottom-0 left-0 h-0.5 w-full bg-wodi-pink"></span>
                            @endif
                        </a>
                    @endforeach
                </nav>
        
                {{-- Desktop CTAs --}}
                <div class="ml-auto hidden shrink-0 items-center gap-3 lg:flex">
                    <a href="#admissions"
                        class="rounded-full bg-wodi-pink px-4 py-2.5 text-[11px] font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                        Enroll Your Child Today
                    </a>
        
                    <a href="#resources"
                        class="rounded-full border border-wodi-pink bg-white/40 px-4 py-2.5 text-[11px] font-medium text-wodi-pink transition-colors hover:bg-wodi-pink hover:text-white">
                Discover Our Programs
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
            @foreach ($navItems as $item)
                <a href="{{ $item['url'] }}"
                   @click="open = false"
                   class="rounded-lg px-3 py-2.5 text-[15px] font-medium text-wodi-ink hover:bg-wodi-pink/5 hover:text-wodi-pink">
                    {{ $item['label'] }}
                </a>
            @endforeach

            <div class="mt-3 flex flex-col gap-2">
                <a href="#admissions" @click="open = false"
                   class="rounded-full bg-wodi-pink px-6 py-3 text-center text-sm font-medium text-white">
                    Enroll Your Child Today
                </a>

                <a href="#resources" @click="open = false"
                   class="rounded-full border border-wodi-pink px-6 py-3 text-center text-sm font-medium text-wodi-pink">
                    Discover Our Programs
                </a>
            </div>
        </nav>
    </div>
</header>
