/**
 * Page-builder preview bridge. Loaded only into the builder's iframe (a page
 * rendered with $editor), never on the public site — see layouts/site.blade.php.
 *
 * Two-way talk with the editor across the frame boundary:
 *   - click a block in the preview  -> tell the editor to select it
 *   - editor sends a selection      -> outline that block and scroll to it
 */
(function () {
    const post = (m) =>
        parent.postMessage({ source: 'cms-preview', ...m }, '*');

    document.querySelectorAll('[data-cms-block]').forEach((el) => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            post({ type: 'select', id: Number(el.dataset.cmsBlock) });
        });
    });

    window.addEventListener('message', (e) => {
        const m = e.data || {};
        if (m.source !== 'cms-editor') return;

        if (m.type === 'select') {
            document
                .querySelectorAll('.cms-selected')
                .forEach((n) => n.classList.remove('cms-selected'));

            const el = document.querySelector(
                '[data-cms-block="' + m.id + '"]',
            );
            if (el) {
                el.classList.add('cms-selected');
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    post({ type: 'ready' });
})();
