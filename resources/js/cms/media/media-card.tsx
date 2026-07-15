import {
    Archive,
    File as FileIcon,
    FileText,
    Film,
    Music,
} from 'lucide-react';
import { cn } from '../lib/cn';
import { MediaItem, MediaKind } from './types';

const KIND_ICON: Record<Exclude<MediaKind, 'image'>, typeof FileIcon> = {
    video: Film,
    audio: Music,
    document: FileText,
    archive: Archive,
    other: FileIcon,
};

function extensionOf(item: MediaItem): string {
    const dot = item.originalName.lastIndexOf('.');
    return dot > -1 ? item.originalName.slice(dot + 1) : item.kind;
}

export function MediaCard({
    item,
    selected = false,
    onClick,
}: {
    item: MediaItem;
    selected?: boolean;
    onClick?: (item: MediaItem) => void;
}) {
    const Icon = item.kind === 'image' ? null : KIND_ICON[item.kind];

    return (
        <button
            type="button"
            onClick={() => onClick?.(item)}
            title={item.originalName}
            className={cn(
                'group flex flex-col overflow-hidden rounded-[4px] border border-black/10 bg-white text-left transition',
                'hover:border-black/25 hover:shadow-sm',
                selected && 'ring-2 ring-neutral-900 ring-offset-1',
            )}
        >
            <div className="relative aspect-square bg-neutral-50">
                {item.kind === 'image' ? (
                    <img
                        src={item.url}
                        alt={item.alt ?? item.originalName}
                        loading="lazy"
                        className="absolute inset-0 h-full w-full object-contain p-2"
                    />
                ) : (
                    Icon && (
                        <div className="absolute inset-0 flex items-center justify-center">
                            <Icon
                                className="size-8 text-neutral-400"
                                strokeWidth={1.5}
                            />
                        </div>
                    )
                )}
            </div>

            <div className="min-w-0 px-2 py-1.5">
                <p className="truncate text-xs font-medium text-neutral-800">
                    {item.title || item.originalName}
                </p>
                <p className="truncate text-[11px] text-neutral-400 uppercase">
                    {extensionOf(item)}
                </p>
            </div>
        </button>
    );
}
