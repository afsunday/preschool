import { Dialog, DialogBackdrop, DialogPanel } from '@headlessui/react';
import { X } from 'lucide-react';
import type { PropsWithChildren } from 'react';
import { cn } from '@/lib/utils';

type MaxWidth = 'sm' | 'md' | 'lg' | 'xl' | '2xl';

const maxWidthClass: Record<MaxWidth, string> = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
};

/**
 * House-style modal (Headless UI, no Radix). Flat 4px radius.
 */
export default function Modal({
    open,
    onClose,
    title,
    maxWidth = 'lg',
    children,
}: PropsWithChildren<{
    open: boolean;
    onClose: () => void;
    title?: string;
    maxWidth?: MaxWidth;
}>) {
    return (
        <Dialog open={open} onClose={onClose} className="relative z-50">
            <DialogBackdrop className="fixed inset-0 bg-black/50" />
            <div className="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel
                    className={cn(
                        'w-full overflow-hidden rounded-[4px] bg-white shadow-xl',
                        maxWidthClass[maxWidth],
                    )}
                >
                    {title && (
                        <div className="flex items-center justify-between border-b border-black/10 px-4 py-3">
                            <h2 className="text-sm font-semibold">{title}</h2>
                            <button
                                type="button"
                                onClick={onClose}
                                className="rounded p-1 text-neutral-400 hover:text-neutral-700"
                            >
                                <X className="size-4" />
                            </button>
                        </div>
                    )}
                    <div className="p-4">{children}</div>
                </DialogPanel>
            </div>
        </Dialog>
    );
}
