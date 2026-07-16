import { useCallback, useState } from 'react';

type Toast = {
    type: string; // 'success' or 'danger'
    message?: string | null;
    key: string;
};

// Custom hook for managing notifications
export const useNotifications = () => {
    const [toasts, setNotifToasts] = useState<Toast[]>([]);
    const defDuration = 7000;

    // Success toast action
    const success = useCallback(
        (message?: string, duration?: number | null) => {
            const key = Math.random().toString(36).slice(2);
            setNotifToasts((prevToasts) => [
                ...prevToasts,
                { type: 'success', message, key },
            ]);
            const dur = duration ? Number(duration) : defDuration;

            setTimeout(() => {
                setNotifToasts((prevToasts) =>
                    prevToasts.filter((toast) => toast.key !== key),
                );
            }, dur);
        },
        [],
    );

    // Error toast action
    const error = useCallback((message?: string, duration?: number | null) => {
        const key = Math.random().toString(36).slice(2);
        setNotifToasts((prevToasts) => [
            ...prevToasts,
            { type: 'danger', message, key },
        ]);
        const dur = duration ? Number(duration) : defDuration;

        setTimeout(() => {
            setNotifToasts((prevToasts) =>
                prevToasts.filter((toast) => toast.key !== key),
            );
        }, dur);
    }, []);

    // Remove a specific toast
    const remove = useCallback((key: string) => {
        setNotifToasts((prevToasts) =>
            prevToasts.filter((toast) => toast.key !== key),
        );
    }, []);

    const setToasts = (
        type: string | 'success' | 'danger',
        message: string,
    ) => {
        setNotifToasts((prev) => [
            ...prev,
            { type, message, key: Math.random().toString(36).slice(2) },
        ]);
    };

    return {
        toasts,
        success,
        setToasts,
        error,
        remove,
    };
};
