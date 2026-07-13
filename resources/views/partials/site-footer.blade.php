@php
    $footerColumns = [
        'WODI Daycare' => [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Admissions', 'url' => '#admissions'],
            ['label' => 'Gallery', 'url' => '#'],
        ],
        'COMPANY' => [
            ['label' => 'About us', 'url' => '#about'],
            ['label' => 'Contact', 'url' => '#'],
            ['label' => 'FAQ', 'url' => '#'],
        ],
        'RESOURCES' => [
            ['label' => 'Resources for Parents & Kids', 'url' => '#resources'],
        ],
    ];
@endphp

<footer class="relative overflow-hidden bg-wodi-cream pt-16">
    {{-- watermark --}}
    <span aria-hidden="true"
          class="pointer-events-none absolute inset-x-0 bottom-0 hidden text-center text-[9rem] leading-none font-extrabold tracking-tight text-white/50 select-none lg:block">
        WODI DAYCARE
    </span>

    <div class="relative mx-auto max-w-[1400px] px-5 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[1fr_repeat(3,minmax(0,1fr))]">
            <a href="{{ route('home') }}" class="shrink-0">
                <img src="/images/brand/logo.png" alt="{{ config('app.name') }}" class="h-12 w-auto">
            </a>

            @foreach ($footerColumns as $heading => $links)
                <div>
                    <h3 class="text-lg font-bold">{{ $heading }}</h3>

                    <ul class="mt-5 space-y-3">
                        @foreach ($links as $link)
                            <li>
                                <a href="{{ $link['url'] }}"
                                   class="text-[15px] text-wodi-ink transition-colors hover:text-wodi-pink">
                                    {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <hr class="mt-12 border-wodi-ink/10">

        <p class="mt-8 max-w-5xl text-sm leading-relaxed text-wodi-ink/80">
            Potter ipsum wand elf parchment wingardium. Vanishing flat should twin for order wormtail invisibility
            blotts viktor. Tell blubber kiss the for blubber wars hagrid. Mimubulus 10 nose dog ollivanders. Locket
            charm knitted this other twin do fanged azkaban metamorphimagus. Banana you-know-who feint where feint.
            Nagini tap-dancing diddykins mischief ministry-of-magic squashy avada dobby hand treacle. Floo letters
            glory us azkaban. Hunt sir cup hearing smile weasley detention pie. Ravenclaw boggarts gamp's holyhead
            cottage in blue. Gringotts sunshine dress
        </p>

        <div class="mt-10 flex flex-col gap-3 pb-10 text-sm sm:flex-row sm:items-center sm:justify-between">
            <p class="font-medium">
                &copy; Copyright {{ date('Y') }}. All Rights Reserved By {{ config('app.name') }}
            </p>

            <div class="flex items-center gap-2">
                <a href="#" class="hover:text-wodi-pink">Terms of Use</a>
                <span class="text-wodi-ink/30">|</span>
                <a href="#" class="hover:text-wodi-pink">Privacy Policy</a>
                <span class="text-wodi-ink/30">|</span>
                <a href="#" class="hover:text-wodi-pink">Cookies Policy</a>
            </div>
        </div>
    </div>
</footer>
