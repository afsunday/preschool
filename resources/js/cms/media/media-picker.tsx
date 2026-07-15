import { useState } from 'react';
import { Dialog, DialogBackdrop, DialogPanel } from '@headlessui/react';
import { ImageOff, Loader2, X } from 'lucide-react';
import { cn } from '../lib/cn';
import { MediaLibrary } from './media-library';
import { MediaApi, MediaItem, MediaKind } from './types';

export interface MediaPickerProps {
    api: MediaApi;
    /** Currently selected item (controlled). */
    value?: MediaItem | null;
    /** True when an id is set but the item is still resolving/unresolved — keeps
     * the picker "filled" so the selection never looks removed. */
    hasValue?: boolean;
    /** Resolving the current item. */
    loading?: boolean;
    /** Restrict the picker to a single kind (e.g. only images). */
    kind?: MediaKind;
    onChange: (item: MediaItem | null) => void;
    onError?: (message: string) => void;
    /** Height of the trigger thumbnail. */
    className?: string;
}

/**
 * A droppable field: shows the chosen media (or an empty slot) and opens the
 * library in a dialog to pick. One library, reused — not a reimplementation.
 */
export function MediaPicker({
    api,
    value = null,
    hasValue = false,
    loading = false,
    kind,
    onChange,
    onError,
    className,
}: MediaPickerProps) {
    const [open, setOpen] = useState(false);
    const filled = Boolean(value) || hasValue;

    return (
        <>
            <div
                className={cn(
                    'relative flex aspect-square w-32 items-center justify-center overflow-hidden rounded-[4px] border border-black/10 bg-neutral-50',
                    className,
                )}
            >
                {value ? (
                    value.kind === 'image' ? (
                        <img
                            src={value.url}
                            alt={value.alt ?? value.originalName}
                            className="absolute inset-0 h-full w-full object-contain p-1"
                        />
                    ) : (
                        <span className="px-2 text-center text-xs text-neutral-500">
                            {value.originalName}
                        </span>
                    )
                ) : (
                    <ImageOff className="size-6 text-neutral-300" />
                )}

                {value && (
                    <button
                        type="button"
                        onClick={() => onChange(null)}
                        className="absolute top-1 right-1 rounded-full bg-black/60 p-0.5 text-white hover:bg-black/80"
                        aria-label="Remove"
                    >
                        <X className="size-3.5" />
                    </button>
                )}

                <button
                    type="button"
                    onClick={() => setOpen(true)}
                    className="absolute inset-x-0 bottom-0 bg-neutral-900/80 py-1 text-center text-[11px] font-medium text-white hover:bg-neutral-900"
                >
                    {value ? 'Change' : 'Select'}
                </button>
            </div>

            <Dialog
                open={open}
                onClose={() => setOpen(false)}
                className="relative z-50"
            >
                <DialogBackdrop className="fixed inset-0 bg-black/50" />
                <div className="fixed inset-0 flex items-center justify-center p-4">
                    <DialogPanel className="flex h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-[4px] bg-white shadow-xl">
                        <div className="flex items-center justify-between border-b border-black/10 px-4 py-3">
                            <h2 className="text-sm font-semibold">
                                Select media
                            </h2>
                            <button
                                type="button"
                                onClick={() => setOpen(false)}
                                className="rounded p-1 text-neutral-400 hover:text-neutral-700"
                            >
                                <X className="size-4" />
                            </button>
                        </div>

                        <div className="min-h-0 flex-1 p-4">
                            <MediaLibrary
                                api={api}
                                mode="select"
                                selectedId={value?.id ?? null}
                                fixedKind={kind}
                                onSelect={(item) => {
                                    onChange(item);
                                    setOpen(false);
                                }}
                                onError={onError}
                            />
                        </div>
                    </DialogPanel>
                </div>
            </Dialog>
        </>
    );
}
