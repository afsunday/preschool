import { Trash2 } from 'lucide-react';
import type { ReactNode } from 'react';
import ActionDialog from './action-dialog';

/**
 * A confirm prompt built on ActionDialog — used in place of window.confirm.
 * Stays mounted and slides in when `open` is true.
 */
export function ConfirmDialog({
    open,
    title,
    message,
    loading = false,
    onConfirm,
    onClose,
}: {
    open: boolean;
    title: ReactNode;
    message?: ReactNode;
    loading?: boolean;
    onConfirm: () => void;
    onClose: () => void;
}) {
    return (
        <ActionDialog
            hidden={open}
            loading={loading}
            btn="red"
            onClose={onClose}
            onAccept={onConfirm}
        >
            <span
                title="icon"
                className="grid size-12 place-items-center rounded-full bg-red-50 text-red-500"
            >
                <Trash2 className="size-5" />
            </span>
            <span title="title">{title}</span>
            {message && (
                <span title="subtitle" className="text-neutral-500">
                    {message}
                </span>
            )}
        </ActionDialog>
    );
}
