import { useCallback, useEffect, useRef, useState } from 'react';
import { MediaApi, MediaItem, MediaKind } from './types';

/**
 * Self-contained list/search/upload state for the library. No react-query, no
 * external store — keeps the folder dependency-free and portable.
 */
export function useMediaList(api: MediaApi) {
    const [items, setItems] = useState<MediaItem[]>([]);
    const [q, setQ] = useState('');
    const [kind, setKind] = useState<MediaKind | 'all'>('all');
    const [loading, setLoading] = useState(false);
    const [nextCursor, setNextCursor] = useState<string | null>(null);
    const [error, setError] = useState<string | null>(null);

    // Guards against out-of-order responses when the query changes rapidly.
    const requestId = useRef(0);

    const load = useCallback(
        async (cursor: string | null = null) => {
            const id = ++requestId.current;
            setLoading(true);
            setError(null);
            try {
                const res = await api.list({ q, kind, cursor });
                if (id !== requestId.current) return; // stale
                setItems((prev) =>
                    cursor ? [...prev, ...res.data] : res.data,
                );
                setNextCursor(res.nextCursor);
            } catch (e) {
                if (id === requestId.current) {
                    setError(e instanceof Error ? e.message : 'Failed to load');
                }
            } finally {
                if (id === requestId.current) setLoading(false);
            }
        },
        [api, q, kind],
    );

    // Debounced reload whenever the search term or kind filter changes.
    useEffect(() => {
        const t = setTimeout(() => load(null), q ? 250 : 0);
        return () => clearTimeout(t);
    }, [load, q]);

    const loadMore = useCallback(() => {
        if (nextCursor && !loading) load(nextCursor);
    }, [nextCursor, loading, load]);

    /** Prepend freshly uploaded items so they appear immediately. */
    const prepend = useCallback((fresh: MediaItem[]) => {
        setItems((prev) => [...fresh, ...prev]);
    }, []);

    const replace = useCallback((updated: MediaItem) => {
        setItems((prev) =>
            prev.map((m) => (m.id === updated.id ? updated : m)),
        );
    }, []);

    const remove = useCallback((id: number) => {
        setItems((prev) => prev.filter((m) => m.id !== id));
    }, []);

    return {
        items,
        q,
        setQ,
        kind,
        setKind,
        loading,
        error,
        hasMore: nextCursor !== null,
        loadMore,
        prepend,
        replace,
        remove,
    };
}
