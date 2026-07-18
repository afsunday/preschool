import { Head, Link } from '@inertiajs/react';
import {
    FileText,
    Image,
    Library,
    Mail,
    Users
    
} from 'lucide-react';
import type {LucideIcon} from 'lucide-react';

type Stats = {
    pages: number;
    materials: number;
    materialsPublished: number;
    media: number;
    subscribers: number;
    messagesUnread: number;
    messagesTotal: number;
};

type RecentMessage = {
    id: number;
    name: string | null;
    email: string;
    isRead: boolean;
    receivedAt: string | null;
};

export default function Dashboard({
    stats,
    recentMessages,
}: {
    stats: Stats;
    recentMessages: RecentMessage[];
}) {
    const cards: {
        label: string;
        value: number;
        hint?: string;
        href: string;
        icon: LucideIcon;
    }[] = [
        {
            label: 'Pages',
            value: stats.pages,
            href: '/admin/pages',
            icon: FileText,
        },
        {
            label: 'Resources',
            value: stats.materials,
            hint: `${stats.materialsPublished} published`,
            href: '/admin/materials',
            icon: Library,
        },
        {
            label: 'Media',
            value: stats.media,
            href: '/admin/media',
            icon: Image,
        },
        {
            label: 'Messages',
            value: stats.messagesTotal,
            hint:
                stats.messagesUnread > 0
                    ? `${stats.messagesUnread} unread`
                    : 'all read',
            href: '/admin/messages',
            icon: Mail,
        },
        {
            label: 'Subscribers',
            value: stats.subscribers,
            href: '/admin/newsletter',
            icon: Users,
        },
    ];

    return (
        <>
            <Head title="Dashboard" />

            <div className="flex h-full flex-col gap-6 p-4">
                <div>
                    <h1 className="text-xl font-semibold tracking-tight">
                        Dashboard
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        An overview of the site's content and enquiries.
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    {cards.map((c) => (
                        <Link
                            key={c.label}
                            href={c.href}
                            className="group rounded-[6px] border border-black/10 bg-white p-4 transition-colors hover:border-black/20"
                        >
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-neutral-500 uppercase">
                                    {c.label}
                                </span>
                                <c.icon className="size-4 text-neutral-400 group-hover:text-neutral-600" />
                            </div>
                            <div className="mt-2 text-3xl font-semibold tracking-tight">
                                {c.value}
                            </div>
                            {c.hint && (
                                <div className="mt-0.5 text-xs text-neutral-400">
                                    {c.hint}
                                </div>
                            )}
                        </Link>
                    ))}
                </div>

                <div className="rounded-[6px] border border-black/10 bg-white">
                    <div className="flex items-center justify-between border-b border-black/10 px-4 py-3">
                        <h2 className="text-sm font-semibold">
                            Recent messages
                        </h2>
                        <Link
                            href="/admin/messages"
                            className="text-wodi-pink text-xs font-medium hover:underline"
                        >
                            View all
                        </Link>
                    </div>

                    {recentMessages.length === 0 ? (
                        <p className="px-4 py-10 text-center text-sm text-neutral-400">
                            No messages yet.
                        </p>
                    ) : (
                        <ul className="divide-y divide-black/5">
                            {recentMessages.map((m) => (
                                <li
                                    key={m.id}
                                    className="flex items-center gap-3 px-4 py-3 text-sm"
                                >
                                    {!m.isRead && (
                                        <span className="bg-wodi-pink size-2 shrink-0 rounded-full" />
                                    )}
                                    <span
                                        className={
                                            m.isRead ? '' : 'font-medium'
                                        }
                                    >
                                        {m.name ?? 'Anonymous'}
                                    </span>
                                    <span className="truncate text-neutral-400">
                                        {m.email}
                                    </span>
                                    <span className="ml-auto shrink-0 text-xs text-neutral-400">
                                        {m.receivedAt}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: '/dashboard' }],
};
