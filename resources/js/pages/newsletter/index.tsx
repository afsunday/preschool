import { Head, router, useForm } from '@inertiajs/react';
import { Mails, PenLine, Search, Send, Trash2, Users } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { createHttpMediaApi } from '@/cms/media';
import ActionDialog from '@/components/action-dialog';
import { ConfirmDialog } from '@/components/confirm-dialog';
import RichTextEditor from '@/components/rich-text-editor';
import SideModal from '@/components/side-modal';
import { cn } from '@/lib/utils';

type Subscriber = {
    id: number;
    email: string;
    active: boolean;
    subscribedAt: string | null;
};

type Campaign = {
    id: number;
    subject: string;
    audience: string;
    recipients: number;
    sentAt: string | null;
};

type Tab = 'compose' | 'sent' | 'subscribers';

const mediaApi = createHttpMediaApi('/admin/media/items');

export default function NewsletterIndex({
    subscribers,
    activeCount,
    campaigns,
}: {
    subscribers: Subscriber[];
    activeCount: number;
    campaigns: Campaign[];
}) {
    const [tab, setTab] = useState<Tab>('compose');
    const [confirmSend, setConfirmSend] = useState(false);
    const [pending, setPending] = useState<Subscriber | null>(null);
    const [selected, setSelected] = useState<number | null>(
        campaigns[0]?.id ?? null,
    );
    const [recipientsOpen, setRecipientsOpen] = useState(false);
    const [search, setSearch] = useState('');

    const active = subscribers.filter((s) => s.active);
    const filtered = active.filter((s) =>
        s.email.toLowerCase().includes(search.trim().toLowerCase()),
    );

    const form = useForm<{
        subject: string;
        body: string;
        audience: 'all' | 'selected';
        recipients: number[];
    }>({ subject: '', body: '', audience: 'all', recipients: [] });

    const recipientCount =
        form.data.audience === 'all'
            ? activeCount
            : form.data.recipients.length;

    const send = () => {
        setConfirmSend(false);
        form.post('/admin/newsletter/send', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setTab('sent');
                toast.success('Newsletter queued');
            },
        });
    };

    const toggleRecipient = (id: number) => {
        const has = form.data.recipients.includes(id);
        form.setData(
            'recipients',
            has
                ? form.data.recipients.filter((r) => r !== id)
                : [...form.data.recipients, id],
        );
    };

    const selectAllShown = () => {
        const ids = filtered.map((s) => s.id);
        form.setData(
            'recipients',
            Array.from(new Set([...form.data.recipients, ...ids])),
        );
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

    const tabs: { id: Tab; label: string; icon: typeof Send }[] = [
        { id: 'compose', label: 'Compose', icon: PenLine },
        { id: 'sent', label: `Sent (${campaigns.length})`, icon: Mails },
        {
            id: 'subscribers',
            label: `Subscribers (${activeCount})`,
            icon: Users,
        },
    ];

    return (
        <>
            <Head title="Newsletter" />

            <div className="flex h-full flex-col gap-5 p-4">
                <div>
                    <h1 className="text-xl font-semibold tracking-tight">
                        Newsletter
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Write, send, and review what went out.
                    </p>
                </div>

                <div className="flex gap-1 border-b border-black/10">
                    {tabs.map((t) => (
                        <button
                            key={t.id}
                            type="button"
                            onClick={() => setTab(t.id)}
                            className={cn(
                                'flex items-center gap-1.5 border-b-2 px-3 py-2 text-sm font-medium transition',
                                tab === t.id
                                    ? 'border-neutral-900 text-neutral-900'
                                    : 'border-transparent text-neutral-400 hover:text-neutral-700',
                            )}
                        >
                            <t.icon className="size-4" />
                            {t.label}
                        </button>
                    ))}
                </div>

                {tab === 'compose' && (
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            setConfirmSend(true);
                        }}
                        className="max-w-3xl space-y-4"
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

                        <div>
                            <span className="text-xs font-medium text-neutral-600">
                                Message
                            </span>
                            <div className="mt-1.5">
                                <RichTextEditor
                                    value={form.data.body}
                                    onChange={(v) => form.setData('body', v)}
                                    enableImage
                                    mediaApi={mediaApi}
                                    minHeight="240px"
                                    placeholder="Write your newsletter… use the image button to pull in a picture from the media library."
                                />
                            </div>
                            {form.errors.body && (
                                <p className="mt-1 text-xs text-red-500">
                                    {form.errors.body}
                                </p>
                            )}
                        </div>

                        <fieldset>
                            <span className="text-xs font-medium text-neutral-600">
                                Send to
                            </span>
                            <div className="mt-1.5 space-y-2">
                                <label className="flex items-center gap-2 text-sm">
                                    <input
                                        type="radio"
                                        className="radio"
                                        checked={form.data.audience === 'all'}
                                        onChange={() =>
                                            form.setData('audience', 'all')
                                        }
                                    />
                                    All active subscribers ({activeCount})
                                </label>
                                <label className="flex items-center gap-2 text-sm">
                                    <input
                                        type="radio"
                                        className="radio"
                                        checked={
                                            form.data.audience === 'selected'
                                        }
                                        onChange={() =>
                                            form.setData('audience', 'selected')
                                        }
                                    />
                                    Selected people
                                </label>
                            </div>

                            {form.data.audience === 'selected' && (
                                <div className="mt-2 flex items-center gap-3">
                                    <button
                                        type="button"
                                        onClick={() => setRecipientsOpen(true)}
                                        className="btn-light inline-flex items-center gap-1.5"
                                    >
                                        <Users className="size-4" /> Choose
                                        recipients
                                    </button>
                                    <span className="text-sm text-neutral-500">
                                        {form.data.recipients.length} selected
                                    </span>
                                </div>
                            )}
                        </fieldset>

                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={
                                    form.processing || recipientCount === 0
                                }
                                className="btn-black inline-flex items-center gap-1.5"
                            >
                                <Send className="size-4" /> Send to{' '}
                                {recipientCount}
                            </button>
                        </div>
                    </form>
                )}

                {tab === 'sent' && (
                    <div className="grid min-h-0 flex-1 gap-4 lg:grid-cols-[280px_1fr]">
                        <div className="overflow-y-auto rounded-[6px] border border-black/10 bg-white">
                            {campaigns.length === 0 ? (
                                <p className="px-4 py-10 text-center text-sm text-neutral-400">
                                    Nothing sent yet.
                                </p>
                            ) : (
                                <ul className="divide-y divide-black/5">
                                    {campaigns.map((c) => (
                                        <li key={c.id}>
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setSelected(c.id)
                                                }
                                                className={cn(
                                                    'w-full px-4 py-3 text-left transition',
                                                    selected === c.id
                                                        ? 'bg-wodi-pink/5'
                                                        : 'hover:bg-neutral-50',
                                                )}
                                            >
                                                <div className="truncate text-sm font-medium">
                                                    {c.subject}
                                                </div>
                                                <div className="mt-0.5 text-xs text-neutral-400">
                                                    {c.recipients} recipient
                                                    {c.recipients === 1
                                                        ? ''
                                                        : 's'}{' '}
                                                    · {c.sentAt}
                                                </div>
                                            </button>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>

                        <div className="min-h-[60vh] overflow-hidden rounded-[6px] border border-black/10 bg-neutral-100">
                            {selected ? (
                                <iframe
                                    key={selected}
                                    title="Newsletter preview"
                                    src={`/admin/newsletter/campaigns/${selected}/preview`}
                                    className="h-full min-h-[60vh] w-full border-0 bg-white"
                                />
                            ) : (
                                <div className="grid h-full min-h-[60vh] place-items-center text-sm text-neutral-400">
                                    Select a newsletter to preview it.
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {tab === 'subscribers' && (
                    <div className="max-w-2xl overflow-hidden rounded-[6px] border border-black/10 bg-white">
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
                                            className={cn(
                                                'size-2 shrink-0 rounded-full',
                                                s.active
                                                    ? 'bg-green-500'
                                                    : 'bg-neutral-300',
                                            )}
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
                )}
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
                    It will be emailed to {recipientCount} subscriber
                    {recipientCount === 1 ? '' : 's'}.
                </span>
            </ActionDialog>

            <ConfirmDialog
                open={pending !== null}
                title="Remove subscriber?"
                message={pending?.email}
                onConfirm={removeSubscriber}
                onClose={() => setPending(null)}
            />

            <SideModal
                hidden={recipientsOpen}
                onClose={() => setRecipientsOpen(false)}
                sizeClassName="xs:max-w-[440px]"
            >
                <span title="title" className="font-semibold">
                    Choose recipients
                </span>

                <div title="body" data-slot="body" className="p-0">
                    <div className="sticky top-0 z-10 border-b border-black/10 bg-white py-3">
                        <div className="flex items-center gap-2 rounded-[4px] border border-black/10 px-3">
                            <Search className="size-4 shrink-0 text-neutral-400" />
                            <input
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search by email…"
                                className="w-full bg-transparent py-2 text-sm focus:outline-none"
                            />
                        </div>
                        <div className="mt-2 flex items-center justify-between text-xs">
                            <span className="text-neutral-500">
                                {form.data.recipients.length} selected ·{' '}
                                {filtered.length} shown
                            </span>
                            <div className="flex gap-3">
                                <button
                                    type="button"
                                    onClick={selectAllShown}
                                    className="text-wodi-pink font-medium hover:underline"
                                >
                                    Select all shown
                                </button>
                                <button
                                    type="button"
                                    onClick={() =>
                                        form.setData('recipients', [])
                                    }
                                    className="text-neutral-500 hover:underline"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>

                    <div className="space-y-0.5 py-2">
                        {filtered.length === 0 ? (
                            <p className="py-6 text-center text-sm text-neutral-400">
                                No subscribers match “{search}”.
                            </p>
                        ) : (
                            filtered.map((s) => (
                                <label
                                    key={s.id}
                                    className="flex cursor-pointer items-center gap-2 rounded-[4px] px-2 py-1.5 text-sm hover:bg-neutral-50"
                                >
                                    <input
                                        type="checkbox"
                                        className="checkbox"
                                        checked={form.data.recipients.includes(
                                            s.id,
                                        )}
                                        onChange={() => toggleRecipient(s.id)}
                                    />
                                    <span className="truncate">{s.email}</span>
                                </label>
                            ))
                        )}
                    </div>
                </div>

                <div
                    title="footer"
                    data-slot="footer"
                    className="border-t border-black/10 p-3"
                >
                    <button
                        type="button"
                        onClick={() => setRecipientsOpen(false)}
                        className="btn-black w-full"
                    >
                        Done · {form.data.recipients.length} selected
                    </button>
                </div>
            </SideModal>
        </>
    );
}

NewsletterIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Newsletter', href: '/admin/newsletter' },
    ],
};
