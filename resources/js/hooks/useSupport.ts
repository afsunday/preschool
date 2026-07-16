/**
 * Minimal helpers ported from the medplus app that the component library needs.
 * Only the pieces the ported components import live here.
 */

/**
 * Append a query parameter to a URL, choosing the correct separator.
 */
export function appendQueryParam(
    url: string | undefined | null,
    param: string,
    value: string = '',
): string {
    const base = url ?? '';
    const separator = base.includes('?') ? '&' : '?';

    return `${base}${separator}${encodeURIComponent(param)}=${encodeURIComponent(value)}`;
}

/**
 * Build a pagination link that preserves the current query string but swaps in
 * the `page` value from a Laravel paginator URL.
 *
 * This app uses Wayfinder (there is no global Ziggy `route()`), so we derive the
 * target from `window.location` instead.
 */
export function pagingUrl(pageUrl?: string | null): string {
    if (!pageUrl) {
        return '#';
    }

    const current = new URL(window.location.href);
    const incoming = new URL(pageUrl, window.location.origin);
    const page = incoming.searchParams.get('page');

    if (page) {
        current.searchParams.set('page', page);
    } else {
        current.searchParams.delete('page');
    }

    return `${current.pathname}${current.search}`;
}
