import { useEffect, useState } from 'react';
import {
    Dialog,
    DialogBackdrop,
    DialogPanel,
} from '@headlessui/react';
import { Loader2, Trash2, X } from 'lucide-react';
import { cn } from '../lib/cn';
import { MediaApi, MediaInUseError, MediaItem, MediaUsage } from './types';

function formatBytes(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    const units = ['KB', 'MB', 'GB'];
    let value = bytes / 1024;
    let i = 0;
    while (value >= 1024 && i < units.length - 1) {
        value /= 1024;
        i++;
    }
    return `${value.toFixed(value < 10 ? 1 : 0)} ${units[i]}`;
}

export function MediaDetails({
    item,
    api,
    onClose,
    onUpdated,
    onDeleted,
    onError,
}: {
    item: MediaItem;
    api: MediaApi;
    onClose: () => void;
    onUpdated: (item: MediaItem) => void;
    onDeleted: (id: number) => void;
    onError?: (message: string) => void;
}) {
    const [title, setTitle] = useState(item.title ?? '');
    const [alt, setAlt] = useState(item.alt ?? '');
    const [description, setDescription] = useState(item.description ?? '');
    const [saving, setSaving] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [usages, setUsages] = useState<MediaUsage[] | null>(null);

    // Reset the form whenever a different item is opened.
    useEffect(() => {
        setTitle(item.title ?? '');
        setAlt(item.alt ?? '');
        setDescription(item.description ?? '');
        setUsages(null);
    }, [item]);

    const dirty =
        title !== (item.title ?? '') ||
        alt !== (item.alt ?? '') ||
        description !== (item.description ?? '');

    const save = async () => {
        setSaving(true);
        try {
            const updated = await api.update(item.id, {
                title: title || null,
                alt: alt || null,
                description: description || null,
            });
            onUpdated(updated);
        } catch (e) {
            onError?.(e instanceof Error ? e.message : 'Could not save');
        } finally {
            setSaving(false);
        }
    };

    const remove = async () => {
        setDeleting(true);
        setUsages(null);
        try {
            await api.destroy(item.id);
            onDeleted(item.id);
            onClose();
        } catch (e) {
            if (e instanceof MediaInUseError) {
                setUsages(e.usages);
            } else {
                onError?.(e instanceof Error ? e.message : 'Could not delete');
            }
        } finally {
            setDeleting(false);
        }
    };

    return (
        <Dialog open onClose={onClose} className="relative z-50">
            <DialogBackdrop className="fixed inset-0 bg-black/50" />
            <div className="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel className="flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-[4px] bg-white shadow-xl">
                    <div className="flex items-center justify-between border-b border-black/10 px-4 py-3">
                        <h2 className="truncate text-sm font-semibold">
                            {item.originalName}
                        </h2>
                        <button
                            type="button"
                            onClick={onClose}
                            className="rounded p-1 text-neutral-400 hover:text-neutral-700"
                        >
                            <X className="size-4" />
                        </button>
                    </div>

                    <div className="grid flex-1 gap-0 overflow-y-auto md:grid-cols-[1fr_20rem]">
                        {/* Preview */}
                        <div className="flex items-center justify-center bg-neutral-50 p-4">
                            {item.kind === 'image' ? (
                                <img
                                    src={item.url}
                                    alt={item.alt ?? item.originalName}
                                    className="max-h-[60vh] w-auto object-contain"
                                />
                            ) : (
                                <a
                                    href={item.url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="text-sm font-medium text-neutral-700 underline"
                                >
                                    Open file
                                </a>
                            )}
                        </div>

                        {/* Metadata + editable fields */}
                        <div className="flex flex-col gap-4 border-t border-black/10 p-4 md:border-t-0 md:border-l">
                            <dl className="grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-neutral-500">
                                <dt>Type</dt>
                                <dd className="text-right text-neutral-800 uppercase">
                                    {item.kind}
                                </dd>
                                <dt>Size</dt>
                                <dd className="text-right text-neutral-800">
                                    {formatBytes(item.size)}
                                </dd>
                                {item.width && item.height ? (
                                    <>
                                        <dt>Dimensions</dt>
                                        <dd className="text-right text-neutral-800">
                                            {item.width}×{item.height}
                                        </dd>
                                    </>
                                ) : null}
                            </dl>

                            <label className="block text-xs font-medium text-neutral-600">
                                Title
                                <input
                                    value={title}
                                    onChange={(e) => setTitle(e.target.value)}
                                    className="mt-1 w-full rounded-[4px] border border-black/10 px-2 py-1.5 text-sm text-neutral-900 outline-none focus:border-neutral-400"
                                />
                            </label>

                            <label className="block text-xs font-medium text-neutral-600">
                                Alt text
                                <input
                                    value={alt}
                                    onChange={(e) => setAlt(e.target.value)}
                                    placeholder="Describe the image for accessibility"
                                    className="mt-1 w-full rounded-[4px] border border-black/10 px-2 py-1.5 text-sm text-neutral-900 outline-none focus:border-neutral-400"
                                />
                            </label>

                            <label className="block text-xs font-medium text-neutral-600">
                                Description
                                <textarea
                                    value={description}
                                    onChange={(e) =>
                                        setDescription(e.target.value)
                                    }
                                    rows={3}
                                    className="mt-1 w-full resize-none rounded-[4px] border border-black/10 px-2 py-1.5 text-sm text-neutral-900 outline-none focus:border-neutral-400"
                                />
                            </label>

                            {usages && (
                                <div className="rounded-[4px] bg-amber-50 p-2 text-xs text-amber-800">
                                    <p className="font-medium">
                                        In use — cannot delete:
                                    </p>
                                    <ul className="mt-1 list-disc pl-4">
                                        {usages.map((u, i) => (
                                            <li key={i}>{u.label}</li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            <div className="mt-auto flex items-center justify-between gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={remove}
                                    disabled={deleting}
                                    className="inline-flex items-center gap-1.5 rounded-[4px] px-2.5 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:opacity-60"
                                >
                                    {deleting ? (
                                        <Loader2 className="size-4 animate-spin" />
                                    ) : (
                                        <Trash2 className="size-4" />
                                    )}
                                    Delete
                                </button>

                                <button
                                    type="button"
                                    onClick={save}
                                    disabled={!dirty || saving}
                                    className={cn(
                                        'inline-flex items-center gap-2 rounded-[4px] bg-neutral-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-800',
                                        (!dirty || saving) &&
                                            'cursor-not-allowed opacity-60',
                                    )}
                                >
                                    {saving && (
                                        <Loader2 className="size-4 animate-spin" />
                                    )}
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                </DialogPanel>
            </div>
        </Dialog>
    );
}
