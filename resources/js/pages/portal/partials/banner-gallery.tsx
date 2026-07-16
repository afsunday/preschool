import {
    Dialog,
    DialogBackdrop,
    DialogPanel,
    DialogTitle,
} from '@headlessui/react';
import { Check, X } from 'lucide-react';
import { useState } from 'react';
import {
    BANNER_CATEGORIES,
    BANNERS,
    bannerUrl,
    findBanner,
} from '@/lib/class-banners';
import { cn } from '@/lib/utils';

/**
 * Pick a class cover, Google Classroom style: category tabs across the top, a
 * scrolling grid of the artwork beneath.
 */
export function BannerGallery({
    open,
    value,
    onPick,
    onClose,
}: {
    open: boolean;
    value: string;
    onPick: (key: string) => void;
    onClose: () => void;
}) {
    const [category, setCategory] = useState<string | null>(null);
    const shown = category
        ? BANNERS.filter((b) => b.category === category)
        : BANNERS;

    const tabs = [
        { key: null as string | null, label: 'All' },
        ...BANNER_CATEGORIES,
    ];

    return (
        <Dialog open={open} onClose={onClose} className="relative z-[60]">
            <DialogBackdrop
                transition
                className="fixed inset-0 bg-black/40 duration-150 data-closed:opacity-0"
            />
            <div className="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel
                    transition
                    className="flex max-h-[86vh] w-full max-w-[900px] flex-col overflow-hidden rounded-[4px] bg-white shadow-s3 duration-150 data-closed:scale-95 data-closed:opacity-0"
                >
                    <div className="flex shrink-0 items-center justify-between px-5 pt-4">
                        <DialogTitle className="text-lg font-bold text-portal-ink">
                            Select class banner
                        </DialogTitle>
                        <button
                            type="button"
                            onClick={onClose}
                            aria-label="Close"
                            className="grid size-9 place-items-center rounded-[4px] bg-portal-field text-portal-ink transition hover:bg-neutral-200"
                        >
                            <X className="size-4.5" />
                        </button>
                    </div>

                    {/* Category tabs — the gallery's spine, as in the reference. */}
                    <div className="shrink-0 [scrollbar-width:none] overflow-x-auto border-b border-portal-line px-5 [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                        <div className="flex items-center gap-6">
                            {tabs.map((tab) => {
                                const active = category === tab.key;

                                return (
                                    <button
                                        key={tab.key ?? 'all'}
                                        type="button"
                                        onClick={() => setCategory(tab.key)}
                                        aria-current={
                                            active ? 'true' : undefined
                                        }
                                        className={cn(
                                            'shrink-0 border-b-2 py-3 text-sm font-bold whitespace-nowrap transition-colors',
                                            active
                                                ? 'border-portal-accent text-portal-accent'
                                                : 'border-transparent text-neutral-500 hover:text-portal-ink',
                                        )}
                                    >
                                        {tab.label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* content-start matters: the grid is flex-1, so without it a
                        short tab (5 banners) has spare height and `align-content`
                        stretches its rows, while the All tab (56) overflows and
                        keeps them natural — the same card rendering two heights
                        depending on which tab you're on. */}
                    <div className="grid flex-1 auto-rows-min grid-cols-1 content-start gap-4 overflow-y-auto p-5 sm:grid-cols-2 lg:grid-cols-3">
                        {shown.map((banner) => {
                            const active = banner.key === value;

                            return (
                                <button
                                    key={banner.key}
                                    type="button"
                                    title={banner.label}
                                    aria-label={banner.label}
                                    aria-pressed={active}
                                    onClick={() => {
                                        onPick(banner.key);
                                        onClose();
                                    }}
                                    className={cn(
                                        'relative overflow-hidden rounded-[4px] transition',
                                        active
                                            ? 'ring-2 ring-portal-ink ring-offset-2'
                                            : 'hover:brightness-105',
                                    )}
                                >
                                    {/* Anchored right, exactly as the class card
                                        crops it. Centred, `cover` would slice off
                                        the right side — which is where every
                                        banner's objects live. */}
                                    <img
                                        src={bannerUrl(banner.key)}
                                        alt=""
                                        loading="lazy"
                                        className="block h-38 w-full object-cover object-right"
                                    />
                                    {active && (
                                        <span className="absolute top-2 left-2 grid size-7 place-items-center rounded-full bg-white/95">
                                            <Check className="size-4 text-portal-ink" />
                                        </span>
                                    )}
                                </button>
                            );
                        })}
                    </div>

                    <div className="flex shrink-0 items-center justify-between border-t border-portal-line px-5 py-3">
                        <p className="text-xs text-neutral-500">
                            {findBanner(value)?.label ??
                                'Nothing selected yet.'}
                        </p>
                        <p className="text-xs text-neutral-400">
                            {shown.length} banners
                        </p>
                    </div>
                </DialogPanel>
            </div>
        </Dialog>
    );
}
