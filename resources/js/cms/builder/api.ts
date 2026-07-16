import type { BuilderApi, PageDoc, SectionSchema } from './types';

/**
 * Default fetch-based adapter (no axios — Inertia 3 dropped it). This is the
 * only host-aware code in the builder folder.
 */
export function createHttpBuilderApi(baseUrl: string): BuilderApi {
    const base = baseUrl.replace(/\/$/, '');

    const json = async (res: Response) => {
        if (!res.ok) {
            const body = await res.json().catch(() => null);

            throw new Error(body?.message ?? `Request failed (${res.status})`);
        }

        return res.json();
    };

    return {
        async schema(): Promise<SectionSchema[]> {
            const r = await fetch(`${base}/schema`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            return (await json(r)).data;
        },

        async getPage(id: number): Promise<PageDoc> {
            const r = await fetch(`${base}/pages/${id}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            return (await json(r)).data;
        },

        async savePage(id: number, doc: PageDoc): Promise<PageDoc> {
            const r = await fetch(`${base}/pages/${id}`, {
                method: 'PUT',
                headers: jsonHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify({
                    title: doc.title,
                    status: doc.status,
                    meta: doc.meta,
                    sections: doc.sections,
                }),
            });

            return (await json(r)).data;
        },

        async renderPage(id: number, doc: PageDoc): Promise<string> {
            const r = await fetch(`${base}/pages/${id}/render`, {
                method: 'POST',
                headers: jsonHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify({
                    title: doc.title,
                    status: doc.status,
                    meta: doc.meta,
                    headerScripts: doc.headerScripts,
                    footerScripts: doc.footerScripts,
                    sections: doc.sections,
                }),
            });

            return (await json(r)).html;
        },

        async options(source: string) {
            const r = await fetch(`${base}/options/${source}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            return (await json(r)).data;
        },
    };
}

function jsonHeaders(): Record<string, string> {
    const headers: Record<string, string> = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    };
    const m = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    if (m) {
        headers['X-XSRF-TOKEN'] = decodeURIComponent(m[1]);
    }

    return headers;
}
