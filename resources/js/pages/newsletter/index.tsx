import { Head, router, useForm } from '@inertiajs/react';
import { Send, Trash2, Users } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import ActionDialog from '@/components/action-dialog';
import { ConfirmDialog } from '@/components/confirm-dialog';

type Subscriber = {
    id: number;
    email: string;
    active: boolean;
    subscribedAt: string | null;
};

type Campaign = {
    id: number;
    subject: string;
    recipients: number;
    sentAt: string | null;
};

export default function NewsletterIndex({
    subscribers,
    activeCount,
    campaigns,
}: {
    subscribers: Subscriber[];
    activeCount: number;
    campaigns: Campaign[];
}) {
    const form = useForm({ subject: '', body: '' });
    const [confirmSend, setConfirmSend] = useState(false);
    const [pending, setPending] = useState<Subscriber | null>(null);

    const send = () => {
        setConfirmSend(false);
        form.post('/admin/newsletter/send', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                toast.success('Newsletter queued');
            },
        });
    };

    const removeSubscriber = () => {
        if (!pending) {
            return;
        }

        router.delete(`/admin/newsletter/subscribers/${pending.id}`, {
            preserveScroll: true,
            onSuccess: () => toast.success('Subscriber removed'),
        });
        setPending(null);
    };

    return (
        <>
            <Head title="Newsletter" />

            <div className="flex h-full flex-col gap-6 p-4">
                <div className="flex items-end justify-between gap-4">
                    <div>
                        <h1 className="text-xl font-semibold tracking-tight">
                            Newsletter
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Write to your subscribers.
                        </p>
                    </div>
                    <div className="flex items-center gap-1.5 rounded-full bg-neutral-100 px-3 py-1.5 text-sm font-medium">
                        <Users className="size-4 text-neutral-500" />
                        {activeCount} active
                    </div>
                </div>

                {/* Compose */}
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        setConfirmSend(true);
                    }}
                    className="rounded-[6px] border border-black/10 bg-white p-5"
                >
                    <label className="block">
                        <span className="text-xs font-medium text-neutral-600">
                            Subject
                        </span>
                        <input
                            className="form-control mt-1.5"
                            value={form.data.subject}
                            onChange={(e) =>
                                form.setData('subject', e.target.value)
                            }
                        />
                        {form.errors.subject && (
                            <p className="mt-1 text-xs text-red-500">
                                {form.errors.subject}
                            </p>
                        )}
                    </label>

                    <label className="mt-4 block">
                        <span className="text-xs font-medium text-neutral-600">
                            Message
                        </span>
                        <textarea
                            className="form-control mt-1.5"
                            rows={8}
                            value={form.data.body}
                            onChange={(e) =>
                                form.setData('body', e.target.value)
                            }
                        />
                        {form.errors.body && (
                            <p className="mt-1 text-xs text-red-500">
                                {form.errors.body}
                            </p>
                        )}
                    </label>

                    <div className="mt-4 flex justify-end">
                        <button
                            type="submit"
                            disabled={form.processing || activeCount === 0}
                            className="btn-black inline-flex items-center gap-1.5"
                        >
                            <Send className="size-4" /> Send to {activeCount}
                        </button>
                    </div>
                </form>

                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Subscribers */}
                    <div className="rounded-[6px] border border-black/10 bg-white">
                        <div className="border-b border-black/10 px-4 py-3">
                            <h2 className="text-sm font-semibold">
                                Subscribers
                            </h2>
                        </div>
                        {subscribers.length === 0 ? (
                            <p className="px-4 py-10 text-center text-sm text-neutral-400">
                                No subscribers yet.
                            </p>
                        ) : (
                            <ul className="divide-y divide-black/5">
                                {subscribers.map((s) => (
                                    <li
                                        key={s.id}
                                        className="flex items-center gap-3 px-4 py-2.5 text-sm"
                                    >
                                        <span
                                            className={
                                                s.active
                                                    ? 'size-2 shrink-0 rounded-full bg-green-500'
                                                    : 'size-2 shrink-0 rounded-full bg-neutral-300'
                                            }
                                            title={
                                                s.active
                                                    ? 'Subscribed'
                                                    : 'Unsubscribed'
                                            }
                                        />
                                        <span className="truncate">
                                            {s.email}
                                        </span>
                                        <span className="ml-auto shrink-0 text-xs text-neutral-400">
                                            {s.subscribedAt}
                                        </span>
                                        <button
                                            type="button"
                                            onClick={() => setPending(s)}
                                            className="shrink-0 rounded-[4px] p-1 text-neutral-400 hover:bg-red-50 hover:text-red-500"
                                        >
                                            <Trash2 className="size-3.5" />
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>

                    {/* Sent campaigns */}
                    <div className="rounded-[6px] border border-black/10 bg-white">
                        <div className="border-b border-black/10 px-4 py-3">
                            <h2 className="text-sm font-semibold">Sent</h2>
                        </div>
                        {campaigns.length === 0 ? (
                            <p className="px-4 py-10 text-center text-sm text-neutral-400">
                                Nothing sent yet.
                            </p>
                        ) : (
                            <ul className="divide-y divide-black/5">
                                {campaigns.map((c) => (
                                    <li
                                        key={c.id}
                                        className="px-4 py-2.5 text-sm"
                                    >
                                        <div className="font-medium">
                                            {c.subject}
                                        </div>
                                        <div className="text-xs text-neutral-400">
                                            {c.recipients} recipient
                                            {c.recipients === 1
                                                ? ''
                                                : 's'} · {c.sentAt}
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </div>
            </div>

            <ActionDialog
                hidden={confirmSend}
                loading={form.processing}
                btn="dark"
                onClose={() => setConfirmSend(false)}
                onAccept={send}
            >
                <span
                    title="icon"
                    className="bg-wodi-pink/10 text-wodi-pink grid size-12 place-items-center rounded-full"
                >
                    <Send className="size-5" />
                </span>
                <span title="title">Send this newsletter?</span>
                <span title="subtitle" className="text-neutral-500">
                    It will be emailed to {activeCount} active subscriber
                    {activeCount === 1 ? '' : 's'}.
                </span>
            </ActionDialog>

            <ConfirmDialog
                open={pending !== null}
                title="Remove subscriber?"
                message={pending?.email}
                onConfirm={removeSubscriber}
                onClose={() => setPending(null)}
            />
        </>
    );
}

NewsletterIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Newsletter', href: '/admin/newsletter' },
    ],
};
