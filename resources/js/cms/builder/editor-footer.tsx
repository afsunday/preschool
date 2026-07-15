import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import { ChevronUp, Loader2 } from 'lucide-react';
import { cn } from '../lib/cn';

/**
 * Sidebar footer — the document actions (status + Save/publish), pinned to the
 * bottom so they're out of the navigation flow.
 */
export function EditorFooter({
    status,
    onToggleStatus,
    onSave,
    saving,
    pagesHref,
}: {
    status: 'draft' | 'published';
    onToggleStatus: () => void;
    onSave: () => void;
    saving: boolean;
    pagesHref: string;
}) {
    return (
        <div className="flex shrink-0 items-center gap-2 bg-[#1e1e1e] px-2.5 py-2.5 text-neutral-300">
            <span
                className={cn(
                    'rounded-[3px] px-1.5 py-0.5 text-[10px] font-semibold tracking-wide uppercase',
                    status === 'published'
                        ? 'bg-emerald-500/15 text-emerald-400'
                        : 'bg-white/10 text-neutral-400',
                )}
            >
                {status}
            </span>

            <div className="ml-auto flex items-center">
                <button
                    type="button"
                    onClick={onSave}
                    disabled={saving}
                    className="inline-flex items-center gap-1.5 rounded-l-[4px] border border-white/10 border-r-white/5 bg-[#333333] px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-[#404040] disabled:opacity-60"
                >
                    {saving && <Loader2 className="size-4 animate-spin" />}
                    {saving ? 'Saving…' : 'Save'}
                </button>
                <Menu as="div" className="relative">
                    <MenuButton className="grid h-[30px] place-items-center rounded-r-[4px] border border-white/10 border-l-transparent bg-[#333333] px-1 text-white transition hover:bg-[#404040]">
                        <ChevronUp className="size-4" />
                    </MenuButton>
                    <MenuItems
                        anchor="top end"
                        className="z-50 mb-1 w-44 rounded-[4px] border border-black/10 bg-white py-1 text-sm text-neutral-800 shadow-lg focus:outline-none"
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
                                href={pagesHref}
                                className="block w-full px-3 py-1.5 text-left data-focus:bg-neutral-100"
                            >
                                Exit to pages
                            </a>
                        </MenuItem>
                    </MenuItems>
                </Menu>
            </div>
        </div>
    );
}
