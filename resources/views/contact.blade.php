@extends('layouts.site')

@section('title', 'Contact Us — ' . config('app.name'))
@section('meta_description', "Have questions or feedback? We're here to help. Send us a message and we'll respond within 24hrs.")

@section('content')

    <section class="relative overflow-hidden bg-wodi-blush pt-24 pb-20 lg:pt-28">
        <img src="/images/patterns/grid.png" alt=""
             class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-60">

        <div class="relative mx-auto grid max-w-[1400px] items-start gap-6 px-4 lg:grid-cols-2 lg:px-8">

            {{-- ---------------- Left: form card ---------------- --}}
            <div class="rounded-3xl bg-white p-8 shadow-sm lg:p-10">
                <h1 class="font-heading max-w-xs text-3xl leading-tight font-bold text-wodi-ink lg:text-[34px]">
                    Let's chat, reach out to us
                </h1>

                <p class="mt-5 text-xs leading-relaxed text-wodi-muted">
                    Have questions or feedback? We're here to help. Send us a message, and we'll respond within 24hrs.
                    For enquires related to partnership, collaboration and investment please reach out to us via email:
                    <a href="mailto:info@wodischools.com" class="text-blue-600 underline underline-offset-2">info@wodischools.com</a>
                </p>

                <hr class="mt-6 border-wodi-ink/10">

                {{-- x-data drives the live 0/300 character counter --}}
                <form action="#" method="POST" x-data="{ count: 0, max: 300 }" class="mt-6">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="block text-xs font-medium text-wodi-ink">First Name</label>
                            <input id="first_name" name="first_name" type="text"
                                   placeholder="Enter first name"
                                   class="mt-2 w-full rounded-full border border-wodi-ink/15 px-5 py-3 text-xs text-wodi-ink placeholder:text-wodi-muted/60 focus:border-wodi-pink focus:outline-none">
                        </div>

                        <div>
                            <label for="last_name" class="block text-xs font-medium text-wodi-ink">Last Name</label>
                            <input id="last_name" name="last_name" type="text"
                                   placeholder="Enter last name"
                                   class="mt-2 w-full rounded-full border border-wodi-ink/15 px-5 py-3 text-xs text-wodi-ink placeholder:text-wodi-muted/60 focus:border-wodi-pink focus:outline-none">
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="email" class="block text-xs font-medium text-wodi-ink">Email*</label>
                        <input id="email" name="email" type="email" required
                               placeholder="Enter Email"
                               class="mt-2 w-full rounded-full border border-wodi-ink/15 px-5 py-3 text-xs text-wodi-ink placeholder:text-wodi-muted/60 focus:border-wodi-pink focus:outline-none">
                    </div>

                    <div class="mt-5">
                        <label for="message" class="block text-xs font-medium text-wodi-ink">Message</label>

                        <div class="relative mt-2">
                            <textarea id="message" name="message" rows="6" maxlength="300"
                                      x-model="$el.value"
                                      @input="count = $event.target.value.length"
                                      placeholder="Type something here..."
                                      class="w-full resize-none rounded-2xl border border-wodi-ink/15 px-5 py-4 text-xs text-wodi-ink placeholder:text-wodi-muted/60 focus:border-wodi-pink focus:outline-none"></textarea>

                            <span class="absolute right-4 bottom-3 text-[10px] text-wodi-muted"
                                  x-text="`${count}/${max}`">0/300</span>
                        </div>
                    </div>

                    <button type="submit"
                            class="mt-6 rounded-full bg-wodi-pink px-10 py-3.5 text-sm font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                        Send Message
                    </button>
                </form>
            </div>

            {{-- ---------------- Right: map + details ---------------- --}}
            <div class="grid gap-6">
                {{-- map placeholder (solid pink until an embed is wired) --}}
                <div class="h-[300px] rounded-3xl bg-wodi-pink lg:h-[440px]"></div>

                <div class="rounded-3xl bg-white p-6 shadow-sm lg:p-8">
                    @php
                        $contacts = [
                            [
                                'icon'  => 'lucide-mail',
                                'title' => 'Email Us',
                                'items' => [['label' => 'email go in here', 'url' => 'mailto:info@wodischools.com']],
                            ],
                            [
                                'icon'  => 'lucide-phone',
                                'title' => 'Call Us',
                                'items' => [
                                    ['label' => '0201-280-9919', 'url' => 'tel:02012809919'],
                                    ['label' => '0201-280-9919', 'url' => 'tel:02012809919'],
                                ],
                            ],
                            [
                                'icon'  => 'lucide-locate-fixed',
                                'title' => 'Find Us',
                                'items' => [['label' => 'South Africa Address: 372 Oak Avenue, Ferndale, Ranburg, Gauteng, 2194', 'url' => '#']],
                            ],
                        ];
                    @endphp

                    <ul class="space-y-6">
                        @foreach ($contacts as $contact)
                            <li class="flex gap-4">
                                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-wodi-cream">
                                    <x-dynamic-component :component="$contact['icon']" class="size-5 text-wodi-ink" />
                                </span>

                                <div class="pt-1">
                                    <p class="text-sm font-semibold text-wodi-ink">{{ $contact['title'] }}</p>

                                    <ul class="mt-1.5 list-inside list-disc space-y-1">
                                        @foreach ($contact['items'] as $item)
                                            <li class="text-xs text-wodi-muted">
                                                <a href="{{ $item['url'] }}"
                                                   class="text-wodi-ink underline underline-offset-2 hover:text-wodi-pink">
                                                    {{ $item['label'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    @include('partials.newsletter')

@endsection
