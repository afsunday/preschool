@extends('layouts.site')

@section('title', $page->meta_title ?: $page->title . ' — ' . config('app.name'))
@section('meta_description', $page->meta_description ?? '')

@if ($page->header_scripts)
    @push('head')
        {!! $page->header_scripts !!}
    @endpush
@endif
@if ($page->footer_scripts)
    @push('scripts')
        {!! $page->footer_scripts !!}
    @endpush
@endif

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

@if ($editor ?? false)
    @push('scripts')
        <style>
            [data-cms-block] {
                outline: 2px solid transparent;
                outline-offset: -2px;
                transition: outline-color .1s;
            }

            [data-cms-block]:hover {
                outline-color: rgba(236, 30, 121, .45);
                cursor: pointer;
            }

            [data-cms-block].cms-selected {
                outline-width: 3px;
                outline-offset: -3px;
                outline-color: #ec1e79;
            }
        </style>
        <script>
            (function() {
                const post = (m) => parent.postMessage({
                    source: 'cms-preview',
                    ...m
                }, '*');
                document.querySelectorAll('[data-cms-block]').forEach((el) => {
                    el.addEventListener('click', (e) => {
                        e.preventDefault();
                        post({
                            type: 'select',
                            id: Number(el.dataset.cmsBlock)
                        });
                    });
                });
                window.addEventListener('message', (e) => {
                    const m = e.data || {};
                    if (m.source !== 'cms-editor') return;
                    if (m.type === 'select') {
                        document.querySelectorAll('.cms-selected').forEach((n) => n.classList.remove(
                            'cms-selected'));
                        const el = document.querySelector('[data-cms-block="' + m.id + '"]');
                        if (el) {
                            el.classList.add('cms-selected');
                            el.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    }
                });
                post({
                    type: 'ready'
                });
            })();
        </script>
    @endpush
@endif
