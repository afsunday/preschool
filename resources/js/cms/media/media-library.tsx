import {
    ChangeEvent,
    DragEvent,
    useCallback,
    useRef,
    useState,
} from 'react';
import { Loader2, Search, Upload, X } from 'lucide-react';
import { cn } from '../lib/cn';
import { MediaCard } from './media-card';
import { MediaApi, MediaItem, MediaKind } from './types';
import { useMediaList } from './use-media-list';

const KIND_CHIPS: { value: MediaKind | 'all'; label: string }[] = [
    { value: 'all', label: 'All' },
    { value: 'image', label: 'Images' },
    { value: 'document', label: 'Documents' },
    { value: 'video', label: 'Videos' },
    { value: 'audio', label: 'Audio' },
    { value: 'archive', label: 'Archives' },
    { value: 'other', label: 'Other' },
];

export interface MediaLibraryProps {
    api: MediaApi;
    /** `select` is used inside the picker dialog; `manage` is the full screen. */
    mode?: 'manage' | 'select';
    /** Fired in `select` mode when a card is clicked. */
    onSelect?: (item: MediaItem) => void;
    selectedId?: number | null;
    onError?: (message: string) => void;
}

export function MediaLibrary({
    api,
    mode = 'manage',
    onSelect,
    selectedId = null,
    onError,
}: MediaLibraryProps) {
    const {
        items,
        q,
        setQ,
        kind,
        setKind,
        loading,
        error,
        hasMore,
        loadMore,
        prepend,
    } = useMediaList(api);

    const [dragging, setDragging] = useState(false);
    const [progress, setProgress] = useState<number | null>(null);
    const fileInput = useRef<HTMLInputElement>(null);

    const upload = useCallback(
        async (files: FileList | File[] | null) => {
            const list = files ? Array.from(files) : [];
            if (list.length === 0) return;
            setProgress(0);
            try {
                const created = await api.upload(list, {
                    onProgress: setProgress,
                });
                prepend(created);
            } catch (e) {
                onError?.(
                    e instanceof Error ? e.message : 'Upload failed',
                );
            } finally {
                setProgress(null);
            }
        },
        [api, prepend, onError],
    );

    const onDrop = (e: DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        setDragging(false);
        upload(e.dataTransfer.files);
    };

    const onBrowse = (e: ChangeEvent<HTMLInputElement>) => {
        upload(e.target.files);
        e.target.value = '';
    };

    const uploading = progress !== null;

    return (
        <div className="flex h-full flex-col gap-4">
            {/* Toolbar: search + upload */}
            <div className="flex flex-wrap items-center gap-3">
                <div className="relative min-w-[200px] flex-1">
                    <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-neutral-400" />
                    <input
                        type="search"
                        value={q}
                        onChange={(e) => setQ(e.target.value)}
                        placeholder="Search by name, alt or description…"
                        className="w-full rounded-lg border border-black/10 bg-white py-2 pr-8 pl-9 text-sm outline-none focus:border-pink-400 dark:border-white/10 dark:bg-neutral-900"
                    />
                    {q && (
                        <button
                            type="button"
                            onClick={() => setQ('')}
                            className="absolute top-1/2 right-2 -translate-y-1/2 rounded p-1 text-neutral-400 hover:text-neutral-700"
                        >
                            <X className="size-4" />
                        </button>
                    )}
                </div>

                <button
                    type="button"
                    onClick={() => fileInput.current?.click()}
                    disabled={uploading}
                    className="inline-flex items-center gap-2 rounded-lg bg-pink-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-pink-700 disabled:opacity-60"
                >
                    {uploading ? (
                        <Loader2 className="size-4 animate-spin" />
                    ) : (
                        <Upload className="size-4" />
                    )}
                    {uploading ? `Uploading ${progress}%` : 'Upload'}
                </button>
                <input
                    ref={fileInput}
                    type="file"
                    multiple
                    hidden
                    onChange={onBrowse}
                />
            </div>

            {/* Kind filter chips */}
            <div className="flex flex-wrap gap-1.5">
                {KIND_CHIPS.map((chip) => (
                    <button
                        key={chip.value}
                        type="button"
                        onClick={() => setKind(chip.value)}
                        className={cn(
                            'rounded-full px-3 py-1 text-xs font-medium transition',
                            kind === chip.value
                                ? 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900'
                                : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300',
                        )}
                    >
                        {chip.label}
                    </button>
                ))}
            </div>

            {/* Drop area + grid. @container so cards reflow off THIS box's
                width, not the viewport — same layout on the page or in a modal. */}
            <div
                onDragOver={(e) => {
                    e.preventDefault();
                    setDragging(true);
                }}
                onDragLeave={(e) => {
                    e.preventDefault();
                    setDragging(false);
                }}
                onDrop={onDrop}
                className={cn(
                    '@container relative flex-1 overflow-y-auto rounded-xl border border-dashed p-3 transition',
                    dragging
                        ? 'border-pink-400 bg-pink-50/60 dark:bg-pink-950/20'
                        : 'border-black/10 dark:border-white/10',
                )}
            >
                {dragging && (
                    <div className="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/70 text-sm font-medium text-pink-700 backdrop-blur-[1px] dark:bg-neutral-900/70">
                        Drop files to upload
                    </div>
                )}

                {items.length === 0 && !loading ? (
                    <div className="flex h-full min-h-40 flex-col items-center justify-center gap-2 text-center text-sm text-neutral-400">
                        <Upload className="size-7" strokeWidth={1.5} />
                        <p>Drag files here, or use the Upload button.</p>
                    </div>
                ) : (
                    <div className="grid grid-cols-2 gap-3 @xs:grid-cols-3 @sm:grid-cols-4 @lg:grid-cols-5 @2xl:grid-cols-6">
                        {items.map((item) => (
                            <MediaCard
                                key={item.id}
                                item={item}
                                selected={selectedId === item.id}
                                onClick={
                                    mode === 'select' ? onSelect : undefined
                                }
                            />
                        ))}
                    </div>
                )}

                {error && (
                    <p className="mt-3 text-center text-sm text-red-500">
                        {error}
                    </p>
                )}

                {hasMore && (
                    <div className="mt-4 flex justify-center">
                        <button
                            type="button"
                            onClick={loadMore}
                            disabled={loading}
                            className="inline-flex items-center gap-2 rounded-lg border border-black/10 px-4 py-2 text-sm font-medium transition hover:bg-neutral-50 disabled:opacity-60 dark:border-white/10 dark:hover:bg-neutral-800"
                        >
                            {loading && (
                                <Loader2 className="size-4 animate-spin" />
                            )}
                            Load more
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
