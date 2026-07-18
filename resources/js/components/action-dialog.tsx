import { Loader2 } from 'lucide-react';
import type { FC, PropsWithChildren } from 'react';
import React, { Children } from 'react';

type ModalProps = PropsWithChildren<{
    hidden?: boolean;
    sizeClassName?: string;
    dismissable?: boolean;
    loading?: boolean;
    onClose?: () => void;
    onAccept?: () => void;
    btn?: string;
    closeClassName?: string;
    sendClassName?: string;
}>;

const ActionDialog: FC<ModalProps> = ({
    hidden = false,
    sizeClassName = 'xs:max-w-[450px]',
    dismissable = true,
    loading = false,
    onClose,
    onAccept,
    children,
    btn = 'dark',
    closeClassName,
    sendClassName,
}) => {
    const handleClose = (
        event: React.MouseEvent<HTMLDivElement, MouseEvent>,
    ) => {
        if (
            (event.target as HTMLElement).classList.contains(
                'modal-backdrop',
            ) &&
            dismissable
        ) {
            onClose?.();
        }
    };

    const childrenArray = Children.toArray(children);

    const findSlot = (name: string) =>
        childrenArray.find(
            (child) =>
                React.isValidElement(child) &&
                ((child.props as Record<string, unknown>).title === name ||
                    (child.props as Record<string, unknown>)['data-slot'] ===
                        name),
        );

    const iconSlot = findSlot('icon');
    const titleSlot = findSlot('title');
    const subtitleSlot = findSlot('subtitle');

    return (
        <div
            className={`modal-backdrop fixed inset-0 z-[50] flex h-full translate-y-full items-center justify-center overflow-x-hidden overflow-y-auto bg-black/20 px-4 backdrop-blur-[2px] transition-transform duration-100 md:translate-x-full md:translate-y-0 ${
                hidden ? '!translate-y-0 md:!translate-x-0' : ''
            }`}
            onClick={handleClose}
        >
            <div
                className={`relative mr-0 box-content flex max-h-full w-full max-w-full flex-col overflow-hidden rounded-lg border bg-white/80 backdrop-blur-sm xs:h-fit ${sizeClassName}`}
            >
                <div className="scroll-sm relative h-full overflow-y-auto px-4 pt-5 pb-8 md:px-6">
                    {iconSlot && (
                        <div className="mb-4 flex items-center justify-center">
                            {iconSlot}
                        </div>
                    )}

                    <span className="mx-auto mb-2 block max-w-[225px] text-center text-xl leading-[1.15] font-semibold">
                        {titleSlot}
                    </span>

                    <span className="block px-5 text-center text-[13px] font-medium">
                        {subtitleSlot}
                    </span>

                    <div className="mt-5 flex justify-center gap-x-3">
                        <button
                            onClick={onClose}
                            type="button"
                            className={`btn-light w-28 cursor-pointer rounded-md border border-neutral-300 px-5 py-2.5 ${closeClassName}`}
                            disabled={loading}
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            className={`btn-${btn} flex w-28 items-center justify-center gap-x-2 rounded-md px-5 py-2.5 ${sendClassName}`}
                            disabled={loading}
                            onClick={onAccept}
                        >
                            <Loader2
                                className={`size-4 animate-spin ${loading ? '' : 'hidden'}`}
                            />
                            <span>Proceed</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ActionDialog;
