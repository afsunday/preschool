import {
    MediaApi,
    MediaInUseError,
    MediaItem,
    MediaListResult,
    MediaPatch,
    MediaQuery,
    UploadOptions,
} from './types';

/**
 * The default HTTP adapter. This function is the ONLY host-aware code in the
 * folder — it assumes a Laravel-style JSON API (resource `{ data }` envelope,
 * cursor pagination `meta.next_cursor`, XSRF cookie). Swap it to retarget.
 *
 *   const api = createHttpMediaApi('/admin/media/items')
 */
export function createHttpMediaApi(baseUrl: string): MediaApi {
    const base = baseUrl.replace(/\/$/, '');

    return {
        async list(query: MediaQuery): Promise<MediaListResult> {
            const params = new URLSearchParams();
            if (query.q) params.set('q', query.q);
            if (query.kind && query.kind !== 'all') params.set('kind', query.kind);
            if (query.cursor) params.set('cursor', query.cursor);

            const res = await fetch(`${base}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) throw await httpError(res);

            const json = await res.json();
            return {
                data: json.data as MediaItem[],
                nextCursor: json.meta?.next_cursor ?? null,
            };
        },

        upload(files: File[], opts?: UploadOptions): Promise<MediaItem[]> {
            const form = new FormData();
            files.forEach((file) => form.append('files[]', file));

            // XHR (not fetch) so we get real upload-progress events.
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', base);
                xhr.responseType = 'json';
                xhr.withCredentials = true;
                xhr.setRequestHeader('Accept', 'application/json');
                const token = xsrfToken();
                if (token) xhr.setRequestHeader('X-XSRF-TOKEN', token);

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable && opts?.onProgress) {
                        opts.onProgress(Math.round((e.loaded / e.total) * 100));
                    }
                };

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve((xhr.response?.data ?? []) as MediaItem[]);
                    } else {
                        reject(
                            new Error(
                                xhr.response?.message ??
                                    `Upload failed (${xhr.status})`,
                            ),
                        );
                    }
                };
                xhr.onerror = () => reject(new Error('Network error during upload'));
                xhr.send(form);
            });
        },

        async update(id: number, patch: MediaPatch): Promise<MediaItem> {
            const res = await fetch(`${base}/${id}`, {
                method: 'PATCH',
                headers: jsonHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify(patch),
            });
            if (!res.ok) throw await httpError(res);
            return (await res.json()).data as MediaItem;
        },

        async destroy(id: number): Promise<void> {
            const res = await fetch(`${base}/${id}`, {
                method: 'DELETE',
                headers: jsonHeaders(),
                credentials: 'same-origin',
            });
            if (res.status === 409) {
                const json = await res.json().catch(() => ({}));
                throw new MediaInUseError(json.usages ?? []);
            }
            if (!res.ok) throw await httpError(res);
        },
    };
}

function jsonHeaders(): Record<string, string> {
    const headers: Record<string, string> = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    };
    const token = xsrfToken();
    if (token) headers['X-XSRF-TOKEN'] = token;
    return headers;
}

/** Read Laravel's XSRF-TOKEN cookie (URL-encoded) for CSRF-protected writes. */
function xsrfToken(): string | null {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : null;
}

async function httpError(res: Response): Promise<Error> {
    const body = await res.json().catch(() => null);
    return new Error(body?.message ?? `Request failed (${res.status})`);
}
