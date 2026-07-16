import { Transition } from '@headlessui/react';
import { usePage } from '@inertiajs/react';
import { Fragment, useEffect, useState } from 'react';
import { useLocalStorage } from 'react-use';
import { useNotifications } from '@/hooks/notificationContext';

type Flash = {
    success?: string | null;
    error?: string | null;
    timestamp?: string | number | null;
};

export const NoticeSnackbar = () => {
    const { flash = {} } = usePage().props as { flash?: Flash };
    const { toasts, remove } = useNotifications();
    const [flashTime, setFlashTime] = useLocalStorage<string | number | null>(
        'notice-flash',
        null,
    );
    const [visibleFlash, setVisibleFlash] = useState<{
        success: string | null;
        error: string | null;
    }>({
        success: flash?.success || null,
        error: flash?.error || null,
    });

    useEffect(() => {
        if (flash.success) {
            setVisibleFlash((prev) => ({
                ...prev,
                success: flash.success ?? null,
            }));

            setTimeout(() => {
                setVisibleFlash((prev) => ({ ...prev, success: null }));

                setFlashTime(flash.timestamp ?? null);
            }, 5000);
        }

        if (flash.error) {
            setVisibleFlash((prev) => ({
                ...prev,
                error: flash.error ?? null,
            }));

            setTimeout(() => {
                setVisibleFlash((prev) => ({ ...prev, error: null }));

                setFlashTime(flash.timestamp ?? null);
            }, 5000);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [flash]);

    return (
        <div>
            <Transition
                as={Fragment}
                show={
                    visibleFlash?.success !== null &&
                    flashTime !== flash.timestamp
                }
                enter="transform transition ease-out duration-300"
                enterFrom="opacity-0 translate-y-4"
                enterTo="opacity-100 translate-y-0"
                leave="transform transition ease-in duration-500"
                leaveFrom="opacity-100 translate-y-0"
                leaveTo="opacity-0 translate-y-2"
            >
                <div className="snackbar fixed top-0 right-0 z-[100] mx-auto pr-3 pl-3 sm:mx-0 sm:pr-4 sm:pl-0">
                    <div className="flex">
                        <nav className="flex-auto">
                            <div className="snack-success rounded-md">
                                <div className="flex items-start">
                                    <div className="px-3 py-5">
                                        <p className="text-[13px] leading-[1.25] font-normal">
                                            {visibleFlash?.success}
                                        </p>
                                    </div>
                                    <button
                                        className="m-auto mr-3 rounded-lg border border-neutral-300 px-2 py-1 text-neutral-300 sm:mr-3"
                                        role="button"
                                        onClick={() =>
                                            setVisibleFlash((prev) => ({
                                                ...prev,
                                                success: null,
                                            }))
                                        }
                                    >
                                        OK
                                    </button>
                                </div>
                            </div>
                        </nav>
                    </div>
                </div>
            </Transition>

            <Transition
                as={Fragment}
                show={
                    visibleFlash?.error !== null &&
                    flashTime !== flash.timestamp
                }
                enter="transform transition ease-out duration-300"
                enterFrom="opacity-0 translate-y-4"
                enterTo="opacity-100 translate-y-0"
                leave="transform transition ease-in duration-500"
                leaveFrom="opacity-100 translate-y-0"
                leaveTo="opacity-0 translate-y-2"
            >
                <div className="snackbar fixed top-0 right-0 z-[100] mx-auto pr-3 pl-3 sm:mx-0 sm:pr-4 sm:pl-0">
                    <div className="flex">
                        <nav className="flex-auto">
                            <div className="snack-danger rounded-md">
                                <div className="flex items-start">
                                    <div className="px-3 py-5">
                                        <p className="text-[13px] leading-[1.25] font-normal">
                                            {visibleFlash?.error}
                                        </p>
                                    </div>
                                    <button
                                        className="m-auto mr-3 rounded-lg border border-neutral-300 px-2 py-1 text-neutral-300 sm:mr-3"
                                        role="button"
                                        onClick={() =>
                                            setVisibleFlash((prev) => ({
                                                ...prev,
                                                error: null,
                                            }))
                                        }
                                    >
                                        OK
                                    </button>
                                </div>
                            </div>
                        </nav>
                    </div>
                </div>
            </Transition>

            {/* Toasts from Store */}
            <div className="fixed top-0 right-0 z-[100] mt-5 flex flex-col sm:mx-4">
                {toasts.map((toast) => (
                    <Transition
                        key={toast.key}
                        as={Fragment}
                        show={true}
                        enter="transform transition ease-out duration-300"
                        enterFrom="opacity-0 translate-y-4"
                        enterTo="opacity-100 translate-y-0"
                        leave="transform transition ease-in duration-1000"
                        leaveFrom="opacity-100 translate-y-0"
                        leaveTo="opacity-0 translate-y-4"
                    >
                        <div className="snackbar z-[100] mx-auto mb-2 !pt-2 pr-3 pl-3 sm:mx-0 sm:pr-4 sm:pl-0">
                            <div className="flex">
                                <nav className="flex-auto">
                                    <div
                                        className={`snack-${toast.type} rounded-md`}
                                    >
                                        <div className="flex items-start">
                                            <div className="px-3 py-5">
                                                <p className="text-[13px] leading-[1.25] font-normal text-black">
                                                    {toast.message}
                                                </p>
                                            </div>
                                            <button
                                                className="m-auto mr-3 rounded-lg border border-neutral-300 bg-transparent px-2 py-1 text-neutral-500 sm:mr-3"
                                                role="button"
                                                onClick={() =>
                                                    remove(toast.key)
                                                }
                                            >
                                                OK
                                            </button>
                                        </div>
                                    </div>
                                </nav>
                            </div>
                        </div>
                    </Transition>
                ))}
            </div>
        </div>
    );
};
