{{-- Shared newsletter band (home + about). --}}
<section class="grid md:grid-cols-2">
    <div class="relative flex flex-col justify-center overflow-hidden bg-wodi-yellow px-8 py-14 lg:px-16">
        <img src="/images/patterns/edu-doodles.png" alt=""
             class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-25 mix-blend-multiply">

        <img src="/images/home/backpack.png" alt=""
             class="pointer-events-none absolute -bottom-4 -left-6 hidden w-40 lg:block">

        <div class="relative lg:pl-28">
            <h2 class="text-3xl font-extrabold text-wodi-ink lg:text-[36px]">Better future for kids</h2>

            <p class="mt-3 max-w-xs text-[15px] text-wodi-ink/80">
                Get the latest on what's going on with WODI daycare.
            </p>

            <form action="#" method="POST" class="mt-6 max-w-md">
                @csrf

                <div class="flex items-center gap-2 rounded-full bg-white p-1.5 pl-5">
                    <label for="newsletter-email" class="sr-only">Your email</label>

                    <input id="newsletter-email" name="email" type="email" required
                           placeholder="Your email"
                           class="min-w-0 flex-1 bg-transparent py-2 text-sm text-wodi-ink placeholder:text-wodi-muted focus:outline-none">

                    <button type="submit"
                            class="shrink-0 rounded-full bg-wodi-pink px-7 py-2.5 text-sm font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                        Send
                    </button>
                </div>
            </form>
        </div>
    </div>

    <img src="/images/home/newsletter-kids.png" alt="Children completing worksheets"
         class="h-64 w-full object-cover md:h-full">
</section>
