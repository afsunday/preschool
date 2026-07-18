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

            {{-- Progressive enhancement: with JS the submit is intercepted and
                 posted via fetch (no reload), swapping the pill for an inline
                 success card. With JS off, this is a plain POST that redirects
                 back with a flash — the @error / session() blocks below. --}}
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="mt-6 max-w-md"
                  @submit.prevent="submit"
                  x-data="{
                      submitting: false,
                      done: false,
                      error: '',
                      async submit() {
                          this.submitting = true;
                          this.error = '';
                          try {
                              const res = await fetch(this.$el.action, {
                                  method: 'POST',
                                  headers: {
                                      'Accept': 'application/json',
                                      'X-Requested-With': 'XMLHttpRequest',
                                  },
                                  body: new FormData(this.$el),
                              });
                              if (res.ok) {
                                  this.done = true;
                                  this.$el.reset();
                              } else if (res.status === 422) {
                                  const data = await res.json().catch(() => null);
                                  this.error = data?.errors?.email?.[0] || 'Please enter a valid email address.';
                              } else {
                                  this.error = 'Something went wrong, please try again.';
                              }
                          } catch (e) {
                              this.error = 'Something went wrong, please try again.';
                          } finally {
                              this.submitting = false;
                          }
                      },
                  }">
                @csrf

                <div x-show="!done" class="flex items-center gap-2 rounded-full bg-white p-1.5 pl-5">
                    <label for="newsletter-email" class="sr-only">Your email</label>

                    <input id="newsletter-email" name="email" type="email" required
                           placeholder="{{ $block->get('placeholder', 'Your email') }}"
                           class="min-w-0 flex-1 bg-transparent py-2 text-sm text-wodi-ink placeholder:text-wodi-muted focus:outline-none">

                    <button type="submit" :disabled="submitting"
                            class="shrink-0 rounded-full bg-wodi-pink px-7 py-2.5 text-sm font-medium text-white transition-colors hover:bg-wodi-pink-dark disabled:cursor-not-allowed disabled:opacity-70">
                        <span x-show="!submitting">{{ $block->get('button', 'Send') }}</span>
                        <span x-show="submitting" x-cloak>Sending…</span>
                    </button>
                </div>

                {{-- JS success state: swaps in for the pill, no reload. --}}
                <div x-show="done" x-cloak x-transition
                     class="flex items-start gap-3 rounded-2xl bg-white p-4 pr-5">
                    <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-wodi-green text-white">
                        <x-lucide-check class="size-4" stroke-width="3" />
                    </span>
                    <p class="text-sm font-medium text-wodi-ink">
                        🎉 You're on the list! Thanks for subscribing to the WODI Daycare newsletter — look out for news, tips and happenings in your inbox.
                    </p>
                </div>

                {{-- JS validation / error message, shown inline without a reload. --}}
                <p x-show="error" x-cloak x-text="error"
                   class="mt-2 text-sm font-medium text-wodi-ink"></p>

                {{-- Non-JS fallback: server-rendered flash + named error bag. --}}
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
