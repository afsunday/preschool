@extends('layouts.site')

@section('title', 'FAQ — ' . config('app.name'))
@section('meta_description', 'Frequently asked questions about WODI Daycare.')

@section('content')

    @php
        $faqs = [
            ['q' => 'Potter ipsum wand elf',                 'a' => 'Potter ipsum wand elf parchment wingardium. Candles ickle 12 charm mellow us hermione. Where bedroom half-moon-glasses parchment spell hollow eyes world portkey chasers. My with wizard last.'],
            ['q' => 'Potter ipsum wand elf parchment wingardium.', 'a' => 'Potter ipsum wand elf parchment wingardium. Palominos dragon-scale niffler them harry. World firs\' snitch spectacles wizard giant motorcycle transfiguration pumpkin. Dog it newt mimubulus tears. Snitch.'],
            ['q' => 'Potter ipsum wand elf parchment wingardium.', 'a' => 'Potter ipsum wand elf parchment wingardium. Who winky cupboard come stairs suits impedimenta ickle. Hedwig not bott\'s i\'d centaur now unicorn. Portkey suits headless blood yew godric\'s captivity armchairs.'],
            ['q' => 'Potter ipsum wand elf parchment',       'a' => 'Potter ipsum wand elf parchment wingardium. 10 phoenix prince armchairs harpies peg-leg that metamorphimagus blood. Glass witch law out minister. Snargaluff do wars blue doe banana daily.'],
            ['q' => 'Potter ipsum wand elf parchment',       'a' => 'Potter ipsum wand elf parchment wingardium. With robes horseless them aragog unicorn raw-steak tap-dancing three-headed. Every red quidditch eeylops squid wool grim. Potion blubber willow harry blue.'],
            ['q' => 'Potter ipsum wand elf parchment',       'a' => 'Potter ipsum wand elf parchment wingardium. Side bathrooms schedule forest harpies not pie restricted mudbloods glory. Horcrux should from bright a. Raw-steak ridgeback 10 floor blue. Mistletoe sorting.'],
            ['q' => 'Potter ipsum wand elf parchment',       'a' => 'Potter ipsum wand elf parchment wingardium. Ridgeback hexed pear-tickle socks cloak 50. That tell nearly-headless fantastic fat. Banquet scarlet phoenix witch disciplinary easy firs\' phoenix petrified.'],
            ['q' => 'Potter ipsum wand elf parchment',       'a' => 'Potter ipsum wand elf parchment wingardium. Flat metamorphimagus hats seek stone spine time-turner doe. Spine points hoops flying frogs tart potter beaters cottage. Winky levicorpus better plums mewing.'],
            ['q' => 'Potter ipsum wand elf parchment',       'a' => 'Potter ipsum wand elf parchment wingardium. Above lights woes mischief phials seven spew crookshanks biting parchment. Start-of-term oddment padfoot bag map. Horcrux letters bathrooms yer.'],
            ['q' => 'Potter ipsum wand elf parchment',       'a' => 'Potter ipsum wand elf parchment wingardium. Potter smile magic remus expecto mrs gillyweed kedavra essence. Tell umbridge pie gillyweed spew the. Come bathrooms us mischief headless headmaster.'],
            ['q' => 'Potter ipsum wand elf parchment',       'a' => 'Potter ipsum wand elf parchment wingardium. Dementors house better disciplinary forbidden love. Flame chalice brass sinistra bott\'s are locomotor gringotts fell cleansweep. Gamp\'s hagrid do I dirigible lupin.'],
            ['q' => 'Potter ipsum wand elf parchment',       'a' => 'Potter ipsum wand elf parchment wingardium. Crookshanks shack hall seeker nose stone filch gillywater petrified bertie. Prophet mr fell out lies chasers. Clean thieves than three-headed phials. Bag feast would.'],
            ['q' => 'Potter ipsum wand elf parchment',       'a' => 'Potter ipsum wand elf parchment wingardium. Bean witch filch newt lived from lights where cupboard. Disciplinary out sight quidditch betrayal essence prophet minerva. Black portrait ickle better bright mrs.'],
            ['q' => 'Potter ipsum wand elf parchment',       'a' => 'Potter ipsum wand elf parchment wingardium. Forest captivity detention on half-moon-glasses your tell locomotor where parchment. Blood tap-dancing now easy them snivellus. Spells hippogriff chalice we.'],
        ];
    @endphp

    {{-- ============================================================
     | HERO
     ============================================================ --}}
    <section class="relative overflow-hidden bg-wodi-blush">
        <img src="/images/patterns/grid.png" alt=""
             class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-60">

        <div class="relative mx-auto max-w-[1400px] px-4 pt-24 pb-10 lg:px-8 lg:pt-28">
            <h1 class="font-heading mx-auto max-w-xl text-center text-3xl leading-tight font-extrabold text-wodi-pink sm:text-4xl lg:text-[46px]">
                Frequently asked questions
            </h1>
        </div>
    </section>

    {{-- ============================================================
     | FAQ GRID — two balanced columns (masonry-style, keeps reading order)
     ============================================================ --}}
    <section class="bg-wodi-blush pb-20">
        <div class="mx-auto max-w-[1250px] px-4 lg:px-8">
            <div class="grid gap-5 md:grid-cols-2">
                @foreach ($faqs as $faq)
                    <article class="flex gap-4 rounded-2xl bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                        <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-wodi-pink/10">
                            <x-lucide-file-question class="size-4 text-wodi-pink" />
                        </span>

                        <div>
                            <h2 class="text-sm font-bold text-wodi-ink">{{ $faq['q'] }}</h2>
                            <p class="mt-2 text-xs leading-relaxed text-wodi-muted">{{ $faq['a'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @include('partials.newsletter')

@endsection
