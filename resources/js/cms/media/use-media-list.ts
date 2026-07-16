import type { InfiniteData } from '@tanstack/react-query';
import {
    useInfiniteQuery,
    useMutation,
    useQueryClient,
} from '@tanstack/react-query';
import { useEffect, useMemo, useState } from 'react';
import type {
    MediaApi,
    MediaItem,
    MediaKind,
    MediaListResult,
    MediaPatch,
} from './types';

type Pages = InfiniteData<MediaListResult, string | null>;

/**
 * Media list/search/upload state, backed by TanStack Query.
 * Reads use useInfiniteQuery (cursor pagination); writes use useMutation and
 * patch the query cache on success. The MediaApi adapter is the transport.
 */
export function useMediaList(api: MediaApi) {
    const qc = useQueryClient();

    const [q, setQ] = useState('');
    const [kind, setKind] = useState<MediaKind | 'all'>('all');
    const [term, setTerm] = useState('');

    // Debounce the search term before it hits the query key.
    useEffect(() => {
        const t = setTimeout(() => setTerm(q), q ? 250 : 0);

        return () => clearTimeout(t);
    }, [q]);

    const queryKey = ['cms-media', term, kind] as const;

    const query = useInfiniteQuery({
        queryKey,
        queryFn: ({ pageParam }) =>
            api.list({ q: term, kind, cursor: pageParam }),
        initialPageParam: null as string | null,
        getNextPageParam: (last: MediaListResult) => last.nextCursor,
    });

    const items = useMemo(
        () => query.data?.pages.flatMap((p) => p.data) ?? [],
        [query.data],
    );

    // --- cache patch helpers (keep the visible list in sync after writes) ---
    const patchPages = (fn: (data: MediaItem[]) => MediaItem[]) =>
        qc.setQueryData<Pages>(queryKey, (old) =>
            old
                ? {
                      ...old,
                      pages: old.pages.map((p) => ({
                          ...p,
                          data: fn(p.data),
                      })),
                  }
                : old,
        );

    const prepend = (fresh: MediaItem[]) =>
        qc.setQueryData<Pages>(queryKey, (old) =>
            old
                ? {
                      ...old,
                      pages: old.pages.map((p, i) =>
                          i === 0 ? { ...p, data: [...fresh, ...p.data] } : p,
                      ),
                  }
                : old,
        );

    const replace = (updated: MediaItem) =>
        patchPages((data) =>
            data.map((m) => (m.id === updated.id ? updated : m)),
        );

    const remove = (id: number) =>
        patchPages((data) => data.filter((m) => m.id !== id));

    // --- mutations ---
    const uploadMutation = useMutation({
        mutationFn: (vars: {
            files: File[];
            onProgress?: (p: number) => void;
        }) => api.upload(vars.files, { onProgress: vars.onProgress }),
        onSuccess: (created) => prepend(created),
    });

    const updateMutation = useMutation({
        mutationFn: (vars: { id: number; patch: MediaPatch }) =>
            api.update(vars.id, vars.patch),
        onSuccess: (updated) => replace(updated),
    });

    const destroyMutation = useMutation({
        mutationFn: (id: number) => api.destroy(id),
        onSuccess: (_data, id) => remove(id),
    });

    return {
        items,
        q,
        setQ,
        kind,
        setKind,
        loading: query.isPending,
        error: query.error ? (query.error as Error).message : null,
        hasMore: Boolean(query.hasNextPage),
        loadMore: () => query.fetchNextPage(),
        isFetchingNextPage: query.isFetchingNextPage,

        // write actions (mutateAsync so callers can await + catch)
        uploadFiles: (files: File[], onProgress?: (p: number) => void) =>
            uploadMutation.mutateAsync({ files, onProgress }),
        updateItem: (id: number, patch: MediaPatch) =>
            updateMutation.mutateAsync({ id, patch }),
        deleteItem: (id: number) => destroyMutation.mutateAsync(id),
    };
}
