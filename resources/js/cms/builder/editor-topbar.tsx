import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import {
    ArrowLeft,
    ExternalLink,
    Loader2,
    LogOut,
    Monitor,
    MoreHorizontal,
    Smartphone,
    Tablet,
} from 'lucide-react';
import { cn } from '../lib/cn';
import { Device } from './preview-frame';

const DEVICES: [Device, typeof Monitor][] = [
    ['desktop', Monitor],
    ['tablet', Tablet],
    ['mobile', Smartphone],
];

export function EditorTopbar({
    title,
    onTitle,
    status,
    onToggleStatus,
    device,
    onDevice,
    onSave,
    saving,
    previewHref,
    pagesHref,
}: {
    title: string;
    onTitle: (v: string) => void;
    status: 'draft' | 'published';
    onToggleStatus: () => void;
    device: Device;
    onDevice: (d: Device) => void;
    onSave: () => void;
    saving: boolean;
    previewHref: string;
    pagesHref: string;
}) {
    return (
        <div className="flex h-12 items-center gap-3 bg-[#23262b] px-3 text-neutral-200">
            {/* Left: back + title */}
            <a
                href={pagesHref}
                className="grid size-8 shrink-0 place-items-center rounded-[6px] text-neutral-400 transition hover:bg-white/10 hover:text-white"
                title="Back to pages"
            >
                <ArrowLeft className="size-4" />
            </a>
            <input
                value={title}
                onChange={(e) => onTitle(e.target.value)}
                className="min-w-0 max-w-[220px] flex-1 truncate rounded-[6px] border border-transparent bg-transparent px-2 py-1 text-sm font-semibold text-white outline-none transition hover:border-white/15 focus:border-white/30"
            />
            <span
                className={cn(
                    'shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium',
                    status === 'published'
                        ? 'bg-green-500/15 text-green-400'
                        : 'bg-white/10 text-neutral-400',
                )}
            >
                {status}
            </span>

            {/* Center: device control */}
            <div className="mx-auto flex items-center gap-0.5 rounded-[8px] bg-white/5 p-0.5">
                {DEVICES.map(([d, Icon]) => (
                    <button
                        key={d}
                        type="button"
                        onClick={() => onDevice(d)}
                        className={cn(
                            'rounded-[6px] p-1.5 transition',
                            device === d
                                ? 'bg-white/15 text-white'
                                : 'text-neutral-500 hover:text-neutral-200',
                        )}
                    >
                        <Icon className="size-4" />
                    </button>
                ))}
            </div>

            {/* Right: save + overflow */}
            <button
                type="button"
                onClick={onSave}
                disabled={saving}
                className="inline-flex shrink-0 items-center gap-2 rounded-[6px] bg-white px-4 py-1.5 text-sm font-semibold text-neutral-900 transition hover:bg-neutral-200 disabled:cursor-not-allowed disabled:opacity-60"
            >
                {saving && <Loader2 className="size-4 animate-spin" />}
                Save
            </button>

            <Menu as="div" className="relative shrink-0">
                <MenuButton className="grid size-8 place-items-center rounded-[6px] text-neutral-400 transition hover:bg-white/10 hover:text-white">
                    <MoreHorizontal className="size-4" />
                </MenuButton>
                <MenuItems
                    anchor="bottom end"
                    className="z-50 mt-1 w-52 rounded-[6px] border border-black/10 bg-white py-1 text-sm text-neutral-800 shadow-lg focus:outline-none"
                >
                    <MenuItem>
                        <button
                            type="button"
                            onClick={onToggleStatus}
                            className="block w-full px-3 py-1.5 text-left data-focus:bg-neutral-100"
                        >
                            {status === 'published'
                                ? 'Switch to draft'
                                : 'Publish'}
                        </button>
                    </MenuItem>
                    <MenuItem>
                        <a
                            href={previewHref}
                            target="_blank"
                            rel="noreferrer"
                            className="flex items-center gap-2 px-3 py-1.5 data-focus:bg-neutral-100"
                        >
                            <ExternalLink className="size-4" /> View page
                        </a>
                    </MenuItem>
                    <div className="my-1 h-px bg-black/10" />
                    <MenuItem>
                        <a
                            href={pagesHref}
                            className="flex items-center gap-2 px-3 py-1.5 data-focus:bg-neutral-100"
                        >
                            <LogOut className="size-4" /> Exit to pages
                        </a>
                    </MenuItem>
                </MenuItems>
            </Menu>
        </div>
    );
}
