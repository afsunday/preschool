import { Head, Link, router, useForm } from '@inertiajs/react';
import { Loader2, MessageSquare, Send } from 'lucide-react';
import type { FormEvent } from 'react';
import { useEffect, useRef } from 'react';
import { cn } from '@/lib/utils';
import type { PortalClass, PortalFamily, PortalMessage } from '@/types/portal';

interface ActiveThread {
    id: number;
    guardian: string;
    messages: PortalMessage[];
}

function Composer({
    classroom,
    thread,
}: {
    classroom: PortalClass;
    thread: ActiveThread;
}) {
    const form = useForm({ body: '' });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post(
            `/portal/classes/${classroom.id}/chats/${thread.id}/messages`,
            {
                preserveScroll: true,
                onSuccess: () => form.reset('body'),
            },
        );
    };

    return (
        <form
            onSubmit={submit}
            className="flex items-center gap-2 border-t border-portal-line p-3"
        >
            <input
                value={form.data.body}
                onChange={(e) => form.setData('body', e.target.value)}
                placeholder="Type a message…"
                className="flex-1 rounded-[4px] border border-portal-line px-4 py-2 text-sm outline-none focus:border-portal-accent"
            />
            <button
                type="submit"
                disabled={form.processing || form.data.body.trim() === ''}
                aria-label="Send"
                className="grid size-9 shrink-0 place-items-center rounded-[4px] bg-portal-accent text-white transition hover:brightness-95 disabled:opacity-50"
            >
                {form.processing ? (
                    <Loader2 className="size-4 animate-spin" />
                ) : (
                    <Send className="size-4" />
                )}
            </button>
        </form>
    );
}

export default function ClassChats({
    classroom,
    families,
    active,
    isStaff,
}: {
    classroom: PortalClass;
    families: PortalFamily[];
    active: ActiveThread | null;
    isStaff: boolean;
}) {
    const endRef = useRef<HTMLDivElement>(null);
    const atBottomRef = useRef(true);

    // Poll so new messages appear without a refresh — no WebSockets needed. Only
    // the `active` + `threads` props are re-fetched; it pauses while the tab is
    // hidden and catches up the moment it's focused again.
    useEffect(() => {
        const poll = () => {
            if (document.visibilityState !== 'visible') {
                return;
            }

            // reload() already forces preserveScroll + preserveState.
            router.reload({ only: ['active', 'families'] });
        };

        const id = window.setInterval(poll, 3000);
        document.addEventListener('visibilitychange', poll);

        return () => {
            window.clearInterval(id);
            document.removeEventListener('visibilitychange', poll);
        };
    }, []);

    // Follow new messages down — but only when the reader is already at the
    // bottom, so a poll never yanks them away from history they're reading.
    useEffect(() => {
        if (atBottomRef.current) {
            endRef.current?.scrollIntoView({ block: 'end' });
        }
    }, [active?.messages.length]);

    return (
        <>
            <Head title={`${classroom.name} · Chats`} />
            <div className="py-5">
                <div className="grid h-[calc(100vh-14rem)] grid-cols-1 overflow-hidden rounded-[4px] border border-portal-line bg-white md:grid-cols-[280px_1fr]">
                    {/* Family list — staff see every family in the room (even ones
                        who've never messaged); a parent sees only their own. */}
                    {isStaff && (
                        <div className="hidden flex-col border-r border-portal-line md:flex">
                            <p className="border-b border-portal-line px-3 py-3 text-xs font-bold tracking-wide text-neutral-400 uppercase">
                                Families
                            </p>
                            <div className="flex-1 overflow-y-auto">
                                {families.length === 0 && (
                                    <p className="p-3 text-xs text-neutral-400">
                                        No families in this room yet.
                                    </p>
                                )}
                                {families.map((family) => (
                                    <Link
                                        key={family.guardianId}
                                        href={
                                            family.conversationId
                                                ? `/portal/classes/${classroom.id}/chats/${family.conversationId}`
                                                : `/portal/classes/${classroom.id}/chats?guardian=${family.guardianId}`
                                        }
                                        className={cn(
                                            'flex items-center gap-2.5 border-b border-portal-line px-3 py-3 transition hover:bg-neutral-50',
                                            family.conversationId != null &&
                                                family.conversationId ===
                                                    active?.id &&
                                                'bg-neutral-100',
                                        )}
                                    >
                                        <span className="grid size-8 shrink-0 place-items-center rounded-full bg-portal-accent/10 text-xs font-bold text-portal-accent">
                                            {family.name.charAt(0)}
                                        </span>
                                        <span className="min-w-0 flex-1">
                                            <span className="block truncate text-sm font-medium text-portal-ink">
                                                {family.name}
                                            </span>
                                            <span className="block truncate text-[11px] text-neutral-400">
                                                {family.lastMessageAt ??
                                                    'No messages'}
                                            </span>
                                        </span>
                                        {family.unread && (
                                            <span className="size-2 shrink-0 rounded-full bg-portal-accent" />
                                        )}
                                    </Link>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Conversation */}
                    {active === null ? (
                        <div className="grid place-items-center p-8 text-center">
                            <div>
                                <MessageSquare className="mx-auto size-8 text-neutral-300" />
                                <p className="mt-3 text-sm font-medium text-portal-ink">
                                    Select a parent to start chatting
                                </p>
                            </div>
                        </div>
                    ) : (
                        <div className="flex min-h-0 flex-col">
                            <div className="border-b border-portal-line px-4 py-3">
                                <p className="text-sm font-bold text-portal-ink">
                                    {isStaff
                                        ? active.guardian
                                        : `${classroom.name}'s room`}
                                </p>
                                <p className="text-xs text-neutral-400">
                                    {isStaff
                                        ? classroom.label
                                        : (classroom.teacher ?? '')}
                                </p>
                            </div>

                            <div
                                onScroll={(e) => {
                                    const el = e.currentTarget;
                                    atBottomRef.current =
                                        el.scrollHeight -
                                            el.scrollTop -
                                            el.clientHeight <
                                        80;
                                }}
                                className="flex-1 space-y-2 overflow-y-auto p-4"
                            >
                                {active.messages.length === 0 && (
                                    <p className="py-8 text-center text-xs text-neutral-400">
                                        No messages yet — say hello.
                                    </p>
                                )}
                                {active.messages.map((message) => (
                                    <div
                                        key={message.id}
                                        className={cn(
                                            'flex',
                                            message.mine
                                                ? 'justify-end'
                                                : 'justify-start',
                                        )}
                                    >
                                        <div
                                            className={cn(
                                                'max-w-[75%] rounded-[4px] px-3 py-2',
                                                message.mine
                                                    ? 'bg-portal-accent text-white'
                                                    : 'bg-neutral-100 text-neutral-800',
                                            )}
                                        >
                                            {!message.mine && (
                                                <p className="text-[11px] font-semibold text-neutral-500">
                                                    {message.author}
                                                </p>
                                            )}
                                            <p className="text-sm whitespace-pre-wrap">
                                                {message.body}
                                            </p>
                                            {message.photos.length > 0 && (
                                                <div className="mt-1.5 flex flex-wrap gap-1.5">
                                                    {message.photos.map(
                                                        (url) => (
                                                            <img
                                                                key={url}
                                                                src={url}
                                                                alt=""
                                                                className="size-24 rounded-[4px] object-cover"
                                                            />
                                                        ),
                                                    )}
                                                </div>
                                            )}
                                            <p
                                                className={cn(
                                                    'mt-0.5 text-[10px]',
                                                    message.mine
                                                        ? 'text-white/60'
                                                        : 'text-neutral-400',
                                                )}
                                            >
                                                {message.at}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                                <div ref={endRef} />
                            </div>

                            <Composer classroom={classroom} thread={active} />
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
