import {
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
    Popover,
    PopoverButton,
    PopoverPanel,
} from '@headlessui/react';
import {
    ChevronDown,
    Eye,
    FileText,
    Grip,
    Monitor,
    Smartphone,
    Tablet,
} from 'lucide-react';
import { cn } from '../lib/cn';
import { Device } from './preview-frame';

type PageRef = { id: number; title: string; slug: string };

const DEVICES: [Device, typeof Monitor, string][] = [
    ['desktop', Monitor, 'Desktop'],
    ['tablet', Tablet, 'Tablet'],
    ['mobile', Smartphone, 'Mobile'],
];

/**
 * The editor's black header — top of the sidebar. Navigation + viewport only;
 * Save/status live in the footer.
 */
export function EditorTopbar({
    title,
    onTitle,
    pages,
    currentId,
    device,
    onDevice,
    previewHref,
    pagesHref,
}: {
    title: string;
    onTitle: (v: string) => void;
    pages: PageRef[];
    currentId: number;
    device: Device;
    onDevice: (d: Device) => void;
    previewHref: string;
    pagesHref: string;
}) {
    const CurrentDevice =
        DEVICES.find(([d]) => d === device)?.[1] ?? Monitor;

    return (
        <div className="flex items-center gap-1 bg-[#1e1e1e] px-2 py-2 text-neutral-300">
            <a
                href={pagesHref}
                title="All pages"
                className="grid size-8 shrink-0 place-items-center rounded-[4px] text-neutral-400 transition hover:bg-white/10 hover:text-white"
            >
                <Grip className="size-[18px]" />
            </a>

            <Popover className="relative w-36 shrink-0">
                    <PopoverButton className="flex w-full items-center gap-1.5 rounded-[4px] bg-white/10 px-2 py-1.5 text-sm font-medium text-white transition outline-none hover:bg-white/20">
                        <span className="min-w-0 flex-1 truncate text-left">
                            {title || 'Untitled'}
                        </span>
                        <ChevronDown className="size-4 shrink-0 text-neutral-400" />
                    </PopoverButton>
                    <PopoverPanel
                        anchor="bottom start"
                        className="z-50 mt-1 w-64 rounded-[4px] border border-black/10 bg-white p-2 text-neutral-800 shadow-xl focus:outline-none"
                    >
                        <label className="block px-1 text-[11px] font-medium text-neutral-500">
                            Page title
                        </label>
                        <input
                            value={title}
                            onChange={(e) => onTitle(e.target.value)}
                            className="mt-1 w-full rounded-[4px] border border-black/10 px-2 py-1.5 text-sm outline-none focus:border-neutral-400"
                        />
                        <div className="my-2 h-px bg-black/10" />
                        <p className="px-1 pb-1 text-[11px] font-medium text-neutral-500">
                            Switch page
                        </p>
                        <div className="max-h-56 overflow-y-auto">
                            {pages.map((p) => (
                                <a
                                    key={p.id}
                                    href={`/admin/pages/${p.id}/edit`}
                                    className={cn(
                                        'flex items-center gap-2 rounded-[4px] px-2 py-1.5 text-sm transition hover:bg-neutral-100',
                                        p.id === currentId &&
                                            'font-semibold text-neutral-900',
                                    )}
                                >
                                    <FileText className="size-3.5 text-neutral-400" />
                                    <span className="flex-1 truncate">
                                        {p.title}
                                    </span>
                                    <span className="text-xs text-neutral-400">
                                        /{p.slug}
                                    </span>
                                </a>
                            ))}
                        </div>
                    </PopoverPanel>
                </Popover>

            {/* Device — single icon, dropdown to switch */}
            <Menu as="div" className="relative ml-auto shrink-0">
                <MenuButton
                    title="Device"
                    className="grid size-8 place-items-center rounded-[4px] bg-white/10 text-white transition outline-none hover:bg-white/20"
                >
                    <CurrentDevice className="size-[18px]" strokeWidth={1.75} />
                </MenuButton>
                <MenuItems
                    anchor="bottom end"
                    className="z-50 mt-1 w-36 rounded-[4px] border border-black/10 bg-white py-1 text-sm text-neutral-800 shadow-lg focus:outline-none"
                >
                    {DEVICES.map(([d, Icon, label]) => (
                        <MenuItem key={d}>
                            <button
                                type="button"
                                onClick={() => onDevice(d)}
                                className={cn(
                                    'flex w-full items-center gap-2 px-3 py-1.5 text-left data-focus:bg-neutral-100',
                                    device === d &&
                                        'font-semibold text-neutral-900',
                                )}
                            >
                                <Icon className="size-4 text-neutral-500" />
                                {label}
                            </button>
                        </MenuItem>
                    ))}
                </MenuItems>
            </Menu>

            <a
                href={previewHref}
                target="_blank"
                rel="noreferrer"
                title="View live page"
                className="grid size-8 shrink-0 place-items-center rounded-[4px] bg-white/10 text-white transition hover:bg-white/20"
            >
                <Eye className="size-[18px]" strokeWidth={1.75} />
            </a>
        </div>
    );
}
