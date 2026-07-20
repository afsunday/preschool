import { Head, Link, router, setLayoutProps, useForm } from '@inertiajs/react';
import {
    ChevronLeft,
    Loader2,
    Megaphone,
    MessageSquare,
    Search,
    Send,
} from 'lucide-react';
import type { FormEvent } from 'react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { avatarColor } from '@/lib/avatar-color';
import { cn } from '@/lib/utils';
import type { PortalClass, PortalFamily, PortalMessage } from '@/types/portal';

interface ActiveThread {
    id: number;
    guardian: string;
    announcement: boolean;
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
    const pollingRef = useRef(false);

    // The backend sends the full list (announcement first, then direct threads).
    const [query, setQuery] = useState('');
    const filtered = useMemo(() => {
        const q = query.trim().toLowerCase();

        return q === ''
            ? families
            : families.filter((t) => t.name.toLowerCase().includes(q));
    }, [families, query]);

    // On mobile, an open conversation fills the screen — tell the layout to hide
    // the bottom tab bar while a thread is open.
    useEffect(() => {
        setLayoutProps({ hideBottomNav: active !== null });

        return () => setLayoutProps({ hideBottomNav: false });
    }, [active]);

    // A background poll must never interrupt the reader with Inertia's full-screen
    // error modal. If a poll hits an HTTP error (a transient 404, an expired
    // session, a stale route cache), swallow it — the next tick retries.
    useEffect(() => {
        const stopHttp = router.on('httpException', (event) => {
            if (pollingRef.current) {
                event.preventDefault();
            }
        });
        const stopNet = router.on('networkError', (event) => {
            if (pollingRef.current) {
                event.preventDefault();
            }
        });

        return () => {
            stopHttp();
            stopNet();
        };
    }, []);

    // Poll so new messages appear without a refresh — no WebSockets needed. Only
    // the `active` + `families` props are re-fetched; it pauses while the tab is
    // hidden and catches up the moment it's focused again.
    useEffect(() => {
        const poll = () => {
            if (document.visibilityState !== 'visible') {
                return;
            }

            pollingRef.current = true;
            // reload() already forces preserveScroll + preserveState.
            router.reload({
                only: ['active', 'families'],
                onFinish: () => {
                    pollingRef.current = false;
                },
            });
        };

        const id = window.setInterval(poll, 3000);
        document.addEventListener('visibilitychange', poll);

        return () => {
            window.clearInterval(id);
            document.removeEventListener('visibilitychange', poll);
        };
    }, []);

    useEffect(() => {
        if (atBottomRef.current) {
            endRef.current?.scrollIntoView({ block: 'end' });
        }
    }, [active?.messages.length]);

    return (
        <>
            <Head title={`${classroom.name} · Chats`} />
            <div className="fixed bottom-0 grid h-[calc(100vh_-_64px)] w-full max-w-[inherit] flex-1 grid-cols-1 grid-rows-1 overflow-hidden border-x border-portal-line bg-white md:h-[calc(100vh_-_120px)] md:grid-cols-[280px_1fr]">
                {families.length > 0 && (
                    <div
                        className={cn(
                            'min-h-0 flex-col border-r border-portal-line md:flex',
                            active ? 'hidden' : 'flex',
                        )}
                    >
                        <div className="border-b border-portal-line p-2">
                            <div className="relative">
                                <Search className="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-neutral-400" />
                                <input
                                    value={query}
                                    onChange={(e) => setQuery(e.target.value)}
                                    placeholder={
                                        isStaff
                                            ? 'Search families'
                                            : 'Search chats'
                                    }
                                    className="w-full rounded-[4px] border border-portal-line py-2 pr-2 pl-8 text-sm outline-none focus:border-portal-accent"
                                />
                            </div>
                        </div>
                        <div className="min-h-0 flex-1 overflow-y-auto">
                            {filtered.length === 0 && (
                                <p className="p-3 text-xs text-neutral-400">
                                    No matches.
                                </p>
                            )}
                            {filtered.map((family) => (
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
                                    {family.isAnnouncement ? (
                                        <span className="grid size-8 shrink-0 place-items-center rounded-full bg-portal-accent/10 text-portal-accent">
                                            <Megaphone className="size-4" />
                                        </span>
                                    ) : (
                                        <span
                                            className={cn(
                                                'grid size-8 shrink-0 place-items-center rounded-full text-xs font-bold',
                                                avatarColor(family.name),
                                            )}
                                        >
                                            {family.name.charAt(0)}
                                        </span>
                                    )}
                                    <span className="min-w-0 flex-1">
                                        <span className="block truncate text-sm font-medium text-portal-ink">
                                            {family.name}
                                        </span>
                                        <span className="block truncate text-[11px] text-neutral-400">
                                            {family.lastMessageAt ??
                                                (family.isAnnouncement
                                                    ? 'Class-wide'
                                                    : 'No messages')}
                                        </span>
                                    </span>
                                </Link>
                            ))}
                        </div>
                    </div>
                )}

                {/* Conversation */}
                {active === null ? (
                    <div className="hidden place-items-center p-8 text-center md:grid">
                        <div>
                            <MessageSquare className="mx-auto size-8 text-neutral-300" />
                            <p className="mt-3 text-sm font-medium text-portal-ink">
                                Select a chat
                            </p>
                        </div>
                    </div>
                ) : (
                    <div className="flex min-h-0 flex-col">
                        <div className="flex items-center gap-3 border-b border-portal-line px-4 py-3">
                            {/* Mobile: back to the list. Staff only — a parent
                                    has a single chat, so there's nothing to go back to. */}
                            {isStaff && (
                                <Link
                                    href={`/portal/classes/${classroom.id}/chats`}
                                    aria-label="Back to chats"
                                    className="-ml-1 grid size-8 shrink-0 place-items-center rounded-[4px] text-neutral-500 transition hover:bg-portal-field md:hidden"
                                >
                                    <ChevronLeft className="size-5" />
                                </Link>
                            )}
                            {active.announcement ? (
                                <span className="grid size-9 shrink-0 place-items-center rounded-full bg-portal-accent/10 text-portal-accent">
                                    <Megaphone className="size-4" />
                                </span>
                            ) : (
                                <span
                                    className={cn(
                                        'grid size-9 shrink-0 place-items-center rounded-full text-sm font-bold',
                                        avatarColor(
                                            isStaff
                                                ? active.guardian
                                                : classroom.name,
                                        ),
                                    )}
                                >
                                    {(isStaff
                                        ? active.guardian
                                        : classroom.name
                                    ).charAt(0)}
                                </span>
                            )}
                            <div className="min-w-0">
                                <p className="truncate text-sm font-bold text-portal-ink">
                                    {active.announcement
                                        ? 'Class announcements'
                                        : isStaff
                                          ? active.guardian
                                          : `${classroom.name}'s room`}
                                </p>
                                <p className="truncate text-xs text-neutral-400">
                                    {active.announcement
                                        ? 'Everyone in the class'
                                        : isStaff
                                          ? classroom.label
                                          : classroom.teachers
                                                .map((t) => t.name)
                                                .join(', ')}
                                </p>
                            </div>
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
                            className="min-h-0 flex-1 space-y-2 overflow-y-auto p-4"
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
                                                {message.photos.map((url) => (
                                                    <img
                                                        key={url}
                                                        src={url}
                                                        alt=""
                                                        className="size-24 rounded-[4px] object-cover"
                                                    />
                                                ))}
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

                        {active.announcement && !isStaff ? (
                            <p className="border-t border-portal-line p-4 text-center text-xs text-neutral-400">
                                Only staff can post class announcements.
                            </p>
                        ) : (
                            <Composer classroom={classroom} thread={active} />
                        )}
                    </div>
                )}
            </div>
        </>
    );
}

ClassChats.layout = { mainClassName: 'px-0 lg:px-6' };
