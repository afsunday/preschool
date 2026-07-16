import type { PropsWithChildren, ReactNode } from 'react';
import React, { Children, useCallback } from 'react';

type ModalProps = PropsWithChildren<{
    hidden: boolean;
    sizeClassName?: string;
    dismissable?: boolean;
    onClose: () => void;
    title?: ReactNode;
    scrollClassName?: string;
}>;

const SideModal: React.FC<ModalProps> = ({
    hidden,
    scrollClassName = 'scroll-sm',
    sizeClassName = 'xs:max-w-[450px]',
    dismissable = true,
    onClose,
    children,
}) => {
    const handleClose = useCallback(
        (event: React.MouseEvent<HTMLDivElement>) => {
            if (
                dismissable &&
                (event.target as HTMLElement).classList.contains(
                    'modal-backdrop',
                )
            ) {
                onClose();
            }
        },
        [dismissable, onClose],
    );

    const childrenArray = Children.toArray(children);

    const findSlot = (name: string) =>
        childrenArray.find(
            (child) =>
                React.isValidElement(child) &&
                ((child.props as Record<string, unknown>).title === name ||
                    (child.props as Record<string, unknown>)['data-slot'] ===
                        name),
        );

    const titleSlot = findSlot('title');
    const bodySlot = findSlot('body');
    const footerSlot = findSlot('footer');

    return (
        <div
            className={`modal-backdrop fixed inset-0 z-[50] flex h-full translate-y-full items-end justify-end overflow-x-hidden overflow-y-auto bg-black/20 backdrop-blur-[2px] transition-transform duration-300 xs:items-end md:translate-x-full md:translate-y-0 ${
                hidden ? '!translate-y-0 md:!translate-x-0' : ''
            }`}
            onClick={handleClose}
        >
            <div
                className={`relative mr-0 flex h-full max-h-full w-full max-w-full flex-col overflow-hidden rounded-t-lg border bg-white shadow-lg xs:rounded-none ${sizeClassName}`}
            >
                <div className="flex items-center justify-between border-b border-gray-200 px-4 py-4">
                    <h3 className="text-default text-base font-medium">
                        {titleSlot}
                    </h3>
                    <button
                        onClick={onClose}
                        className="hover:text-default inline-flex items-center rounded-lg bg-transparent p-1.5 text-sm hover:bg-gray-200 ltr:ml-auto rtl:mr-auto"
                        type="button"
                    >
                        <svg
                            className="h-5 w-5 text-neutral-500"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                fillRule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clipRule="evenodd"
                            ></path>
                        </svg>
                    </button>
                </div>

                <div
                    className={`wb-scroll-sm ${scrollClassName} relative h-full overflow-y-auto px-4 pt-5 pb-8 md:px-6`}
                >
                    {bodySlot}
                </div>
                <div className="border-t">{footerSlot}</div>
            </div>
        </div>
    );
};

export default SideModal;
