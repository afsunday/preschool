{{-- The newsletter band. Site chrome: one global block, rendered by the layout
     on every page, so its copy can never drift between pages.

     The copy comes from the block's settings; the doodles, backpack, photo and
     the form itself are design and stay here. --}}
<section class="grid md:grid-cols-2">
    <div class="relative flex flex-col justify-center overflow-hidden bg-wodi-yellow px-8 py-14 lg:px-16">
        <img src="/images/patterns/edu-doodles.png" alt=""
             class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-25 mix-blend-multiply">

        <img src="/images/home/backpack.png" alt=""
             class="pointer-events-none absolute -bottom-4 -left-6 hidden w-40 lg:block">

        <div class="relative lg:pl-28">
            <h2 class="text-3xl font-extrabold text-wodi-ink lg:text-[36px]">
                {{ $block->get('heading', 'Better future for kids') }}
            </h2>

            @if ($block->get('subtext'))
                <p class="mt-3 max-w-xs text-[15px] text-wodi-ink/80">
                    {{ $block->get('subtext') }}
                </p>
            @endif

            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="mt-6 max-w-md">
                @csrf

                <div class="flex items-center gap-2 rounded-full bg-white p-1.5 pl-5">
                    <label for="newsletter-email" class="sr-only">Your email</label>

                    <input id="newsletter-email" name="email" type="email" required
                           placeholder="{{ $block->get('placeholder', 'Your email') }}"
                           class="min-w-0 flex-1 bg-transparent py-2 text-sm text-wodi-ink placeholder:text-wodi-muted focus:outline-none">

                    <button type="submit"
                            class="shrink-0 rounded-full bg-wodi-pink px-7 py-2.5 text-sm font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                        {{ $block->get('button', 'Send') }}
                    </button>
                </div>

                @error('email', 'newsletter')
                    <p class="mt-2 text-sm font-medium text-wodi-ink">{{ $message }}</p>
                @enderror

                @if (session('newsletterSuccess'))
                    <p class="mt-2 text-sm font-medium text-wodi-ink">{{ session('newsletterSuccess') }}</p>
                @endif
            </form>
        </div>
    </div>

    <img src="{{ $block->mediaUrl('image') ?? '/images/home/newsletter-kids.png' }}"
         alt="{{ $block->mediaAlt('image') ?? 'Children completing worksheets' }}"
         class="h-64 w-full object-cover md:h-full">
</section>
