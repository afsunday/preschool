@extends('layouts.site')

@section('title', $page->meta_title ?: $page->title . ' — ' . config('app.name'))
@section('meta_description', $page->meta_description ?? '')

@section('content')
    @foreach ($blocks as $block)
        @if ($editor ?? false)
            <div data-cms-block="{{ $block->id }}">
        @endif

        @switch ($block->type)
            {{-- The form card and the map/details column are the two halves of one
               | grid, so they are one block — splitting them would break the row. --}}
            @case('contact_form')
                @php
                    $icon = fn($k) => match ($k) {
                        'mail' => 'lucide-mail',
                        'phone' => 'lucide-phone',
                        'locate' => 'lucide-locate-fixed',
                        default => 'lucide-mail',
                    };
                @endphp

                <section class="relative overflow-hidden bg-wodi-blush pt-24 pb-20 lg:pt-28">
                    <img src="/images/patterns/grid.png" alt=""
                         class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-60">

                    <div class="relative mx-auto grid max-w-[1400px] items-start gap-6 px-4 lg:grid-cols-2 lg:px-8">

                        {{-- ---------------- Left: form card ---------------- --}}
                        <div class="rounded-3xl bg-white p-8 shadow-sm lg:p-10">
                            <h1 class="font-heading max-w-xs text-3xl leading-tight font-bold text-wodi-ink lg:text-[34px]">
                                {{ $block->get('title') }}
                            </h1>

                            {{-- links come from the editor unstyled, so the anchor styling is
                               | applied from here (Tailwind can't scan the DB). --}}
                            <p class="mt-5 text-xs leading-relaxed text-wodi-muted [&_a]:text-blue-600 [&_a]:underline [&_a]:underline-offset-2">
                                {!! $block->get('body') !!}
                            </p>

                            <hr class="mt-6 border-wodi-ink/10">

                            @if (session('contactSuccess'))
                                <div class="mt-6 rounded-2xl border border-wodi-pink/30 bg-wodi-pink/5 px-5 py-4 text-xs font-medium text-wodi-pink">
                                    {{ session('contactSuccess') }}
                                </div>
                            @endif

                            {{-- x-data drives the live 0/300 character counter --}}
                            <form action="{{ route('contact.submit') }}" method="POST"
                                  x-data="{ count: {{ strlen((string) old('message')) }}, max: 300 }" class="mt-6">
                                @csrf

                                <div class="grid gap-5 sm:grid-cols-2">
                                    <div>
                                        <label for="first_name" class="block text-xs font-medium text-wodi-ink">First Name</label>
                                        <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}"
                                               placeholder="Enter first name"
                                               class="mt-2 w-full rounded-full border border-wodi-ink/15 px-5 py-3 text-xs text-wodi-ink placeholder:text-wodi-muted/60 focus:border-wodi-pink focus:outline-none">
                                    </div>

                                    <div>
                                        <label for="last_name" class="block text-xs font-medium text-wodi-ink">Last Name</label>
                                        <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}"
                                               placeholder="Enter last name"
                                               class="mt-2 w-full rounded-full border border-wodi-ink/15 px-5 py-3 text-xs text-wodi-ink placeholder:text-wodi-muted/60 focus:border-wodi-pink focus:outline-none">
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <label for="email" class="block text-xs font-medium text-wodi-ink">Email*</label>
                                    <input id="email" name="email" type="email" required value="{{ old('email') }}"
                                           placeholder="Enter Email"
                                           class="mt-2 w-full rounded-full border border-wodi-ink/15 px-5 py-3 text-xs text-wodi-ink placeholder:text-wodi-muted/60 focus:border-wodi-pink focus:outline-none">
                                    @error('email')
                                        <p class="mt-2 text-[11px] text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mt-5">
                                    <label for="message" class="block text-xs font-medium text-wodi-ink">Message</label>

                                    <div class="relative mt-2">
                                        <textarea id="message" name="message" rows="6" maxlength="300"
                                                  @input="count = $event.target.value.length"
                                                  placeholder="Type something here..."
                                                  class="w-full resize-none rounded-2xl border border-wodi-ink/15 px-5 py-4 text-xs text-wodi-ink placeholder:text-wodi-muted/60 focus:border-wodi-pink focus:outline-none">{{ old('message') }}</textarea>

                                        <span class="absolute right-4 bottom-3 text-[10px] text-wodi-muted"
                                              x-text="`${count}/${max}`">0/300</span>
                                    </div>
                                    @error('message')
                                        <p class="mt-2 text-[11px] text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                        class="mt-6 rounded-full bg-wodi-pink px-10 py-3.5 text-sm font-medium text-white transition-colors hover:bg-wodi-pink-dark">
                                    {{ $block->get('submit_label', 'Send Message') }}
                                </button>
                            </form>
                        </div>

                        {{-- ---------------- Right: map + details ---------------- --}}
                        <div class="grid gap-6">
                            {{-- map placeholder (solid pink until an embed is wired) --}}
                            <div class="h-[300px] rounded-3xl bg-wodi-pink lg:h-[440px]"></div>

                            <div class="rounded-3xl bg-white p-6 shadow-sm lg:p-8">
                                <ul class="space-y-6">
                                    @foreach ($block->get('contacts', []) as $contact)
                                        <li class="flex gap-4">
                                            <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-wodi-cream">
                                                <x-dynamic-component :component="$icon(data_get($contact, 'icon'))" class="size-5 text-wodi-ink" />
                                            </span>

                                            <div class="pt-1">
                                                <p class="text-sm font-semibold text-wodi-ink">{{ data_get($contact, 'title') }}</p>

                                                <ul class="mt-1.5 list-inside list-disc space-y-1">
                                                    @foreach (data_get($contact, 'items', []) as $item)
                                                        <li class="text-xs text-wodi-muted">
                                                            <a href="{{ data_get($item, 'url', '#') }}"
                                                               class="text-wodi-ink underline underline-offset-2 hover:text-wodi-pink">
                                                                {{ data_get($item, 'label') }}
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
            @break
        @endswitch

        @if ($editor ?? false)
            </div>
        @endif
    @endforeach
@endsection
