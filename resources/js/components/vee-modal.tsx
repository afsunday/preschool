import React, { Children, PropsWithChildren, ReactNode, useCallback } from 'react';

type ModalProps = PropsWithChildren<{
    hidden: boolean;
    sizeClassName?: string;
    dismissable?: boolean;
    onClose: () => void;
    title?: ReactNode;
}>;

const VeeModal: React.FC<ModalProps> = ({ hidden, sizeClassName = 'xs:max-w-[450px]', dismissable = true, onClose, children }) => {
    const handleClose = useCallback(
        (event: React.MouseEvent<HTMLDivElement>) => {
            if (dismissable && (event.target as HTMLElement).classList.contains('modal-backdrop')) {
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
                ((child.props as Record<string, unknown>).title === name || (child.props as Record<string, unknown>)['data-slot'] === name),
        );

    const titleSlot = findSlot('title');
    const bodySlot = findSlot('body');
    const footerSlot = findSlot('footer');

    return (
        <div
            className={`modal-backdrop backdrop-blur-[2px] fixed inset-0 z-[50] flex h-full translate-y-full items-end justify-center overflow-y-auto overflow-x-hidden bg-black bg-opacity-50 transition-transform duration-200 xs:items-center xs:py-8 md:translate-x-full md:translate-y-0 md:duration-0 ${
                hidden ? '!translate-y-0 md:!translate-x-0' : ''
            }`}
            onClick={handleClose}
        >
            <div className={`relative mr-0 flex max-h-full w-full max-w-full flex-col overflow-hidden border bg-white shadow-lg xs:h-fit xs:rounded-lg ${sizeClassName}`}>
                <div className="flex items-center justify-between border-b border-gray-200 px-4 py-4">
                    <h3 className="text-default text-base font-medium">{titleSlot}</h3>
                    <button
                        onClick={onClose}
                        className="hover:text-default inline-flex items-center rounded-lg bg-transparent p-1.5 text-sm hover:bg-gray-200 ltr:ml-auto rtl:mr-auto"
                        type="button"
                    >
                        <svg className="h-5 w-5 text-neutral-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path
                                fillRule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clipRule="evenodd"
                            ></path>
                        </svg>
                    </button>
                </div>

                <div className="scroll-sm relative h-full overflow-y-auto px-4 pb-8 pt-5 md:px-6">{bodySlot}</div>
                <div className="border-t">{footerSlot}</div>
            </div>
        </div>
    );
};

export default VeeModal;
