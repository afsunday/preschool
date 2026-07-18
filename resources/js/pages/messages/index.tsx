import { Head, router } from '@inertiajs/react';
import { Check, Mail, MailOpen, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { ConfirmDialog } from '@/components/confirm-dialog';

type Submission = {
    id: number;
    name: string | null;
    email: string;
    message: string | null;
    isRead: boolean;
    receivedAt: string | null;
};

export default function MessagesIndex({
    submissions,
    unread,
}: {
    submissions: Submission[];
    unread: number;
}) {
    const [pending, setPending] = useState<Submission | null>(null);

    const toggleRead = (s: Submission) => {
        router.patch(`/admin/messages/${s.id}`, {}, { preserveScroll: true });
    };

    const destroy = () => {
        if (!pending) {
            return;
        }

        router.delete(`/admin/messages/${pending.id}`, {
            preserveScroll: true,
            onSuccess: () => toast.success('Message deleted'),
        });
        setPending(null);
    };

    return (
        <>
            <Head title="Messages" />

            <div className="flex h-full flex-col gap-6 p-4">
                <div>
                    <h1 className="text-xl font-semibold tracking-tight">
                        Messages
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Enquiries from the contact form.
                        {unread > 0 && (
                            <span className="ml-1 font-medium text-neutral-900">
                                {unread} unread.
                            </span>
                        )}
                    </p>
                </div>

                {submissions.length === 0 ? (
                    <div className="rounded-[6px] border border-dashed border-black/15 py-16 text-center text-sm text-neutral-400">
                        No messages yet.
                    </div>
                ) : (
                    <div className="flex flex-col gap-2">
                        {submissions.map((s) => (
                            <div
                                key={s.id}
                                className={
                                    'flex items-start gap-4 rounded-[6px] border p-4 ' +
                                    (s.isRead
                                        ? 'border-black/10 bg-white'
                                        : 'border-wodi-pink/30 bg-wodi-pink/[0.03]')
                                }
                            >
                                <div className="mt-0.5 shrink-0 text-neutral-400">
                                    {s.isRead ? (
                                        <MailOpen className="size-5" />
                                    ) : (
                                        <Mail className="text-wodi-pink size-5" />
                                    )}
                                </div>

                                <div className="min-w-0 flex-1">
                                    <div className="flex flex-wrap items-baseline gap-x-2">
                                        <span className="font-medium">
                                            {s.name ?? 'Anonymous'}
                                        </span>
                                        <a
                                            href={`mailto:${s.email}`}
                                            className="text-wodi-pink text-sm hover:underline"
                                        >
                                            {s.email}
                                        </a>
                                        <span className="ml-auto text-xs text-neutral-400">
                                            {s.receivedAt}
                                        </span>
                                    </div>

                                    {s.message && (
                                        <p className="mt-1.5 text-sm whitespace-pre-line text-neutral-700">
                                            {s.message}
                                        </p>
                                    )}
                                </div>

                                <div className="flex shrink-0 items-center gap-1">
                                    <button
                                        type="button"
                                        onClick={() => toggleRead(s)}
                                        title={
                                            s.isRead
                                                ? 'Mark unread'
                                                : 'Mark read'
                                        }
                                        className="rounded-[4px] p-1.5 text-neutral-500 hover:bg-neutral-100"
                                    >
                                        {s.isRead ? (
                                            <Mail className="size-4" />
                                        ) : (
                                            <Check className="size-4" />
                                        )}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setPending(s)}
                                        className="rounded-[4px] p-1.5 text-neutral-500 hover:bg-red-50 hover:text-red-500"
                                    >
                                        <Trash2 className="size-4" />
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            <ConfirmDialog
                open={pending !== null}
                title="Delete this message?"
                message="This cannot be undone."
                onConfirm={destroy}
                onClose={() => setPending(null)}
            />
        </>
    );
}

MessagesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Messages', href: '/admin/messages' },
    ],
};
