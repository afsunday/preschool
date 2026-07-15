<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title }} — preview</title>
    @fonts
    @vite(['resources/css/site.css'])
    @if ($editor)
        <style>
            [data-section-id] { position: relative; }
            body.cms-editing [data-section-id] { cursor: pointer; outline: 2px solid transparent; outline-offset: -2px; transition: outline-color .1s; }
            body.cms-editing [data-section-id]:hover { outline-color: rgba(236,30,121,.4); }
            body.cms-editing [data-section-id].cms-selected { outline-color: #ec1e79; }
        </style>
    @endif
</head>
<body @class(['cms-editing' => $editor])>
    <div id="cms-sections">
        @foreach ($sections as $section)
            <div data-section-id="{{ $section['id'] }}">{!! $section['html'] !!}</div>
        @endforeach
    </div>

    @if ($editor)
        <script>
            (function () {
                const root = document.getElementById('cms-sections');
                const post = (msg) => parent.postMessage({ source: 'cms-preview', ...msg }, '*');

                function wire(el) {
                    el.addEventListener('click', (e) => {
                        e.preventDefault();
                        post({ type: 'select', id: Number(el.dataset.sectionId) });
                    });
                }
                function wrap(id, html) {
                    const div = document.createElement('div');
                    div.dataset.sectionId = id;
                    div.innerHTML = html;
                    wire(div);
                    return div;
                }
                root.querySelectorAll('[data-section-id]').forEach(wire);

                // Commands from the editor.
                window.addEventListener('message', (e) => {
                    const m = e.data || {};
                    if (m.source !== 'cms-editor') return;
                    const at = (id) => root.querySelector('[data-section-id="' + id + '"]');

                    if (m.type === 'update') {
                        const el = at(m.id);
                        if (el) el.innerHTML = m.html;
                    } else if (m.type === 'remove') {
                        at(m.id)?.remove();
                    } else if (m.type === 'insert') {
                        const node = wrap(m.id, m.html);
                        const ref = m.beforeId ? at(m.beforeId) : null;
                        ref ? root.insertBefore(node, ref) : root.appendChild(node);
                    } else if (m.type === 'reorder') {
                        m.order.forEach((id) => { const el = at(id); if (el) root.appendChild(el); });
                    } else if (m.type === 'select') {
                        root.querySelectorAll('.cms-selected').forEach((n) => n.classList.remove('cms-selected'));
                        at(m.id)?.classList.add('cms-selected');
                        at(m.id)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });

                post({ type: 'ready' });
            })();
        </script>
    @endif
</body>
</html>
