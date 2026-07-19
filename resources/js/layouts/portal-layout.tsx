import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import { Link, usePage } from '@inertiajs/react';
import {
    ArrowLeftRight,
    ChevronsUpDown,
    ClipboardList,
    GraduationCap,
    House,
    MessageSquare,
    Newspaper,
    Settings,
    Users,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import type { Auth } from '@/types/auth';
import type { PortalClass } from '@/types/portal';

type TabDef = { title: string; path: string; icon: LucideIcon };

// Tabs live *inside* a class — there is no global "Students" section, because
// nothing in a daycare is addressed to a child directly.
const classTabs: TabDef[] = [
    { title: 'Feed', path: '', icon: Newspaper },
    { title: 'Students', path: '/students', icon: Users },
    { title: 'Today', path: '/today', icon: ClipboardList },
    { title: 'Chats', path: '/chats', icon: MessageSquare },
];

function Brand() {
    return (
        <Link
            href="/portal"
            className="inline-flex items-center rounded-[4px] bg-portal-brand px-2.5 py-1 text-lg font-extrabold tracking-tight text-white"
        >
            WODI
        </Link>
    );
}

/** The navy pill from the reference: which room am I in, and switch. */
function ClassSwitcher({
    classes,
    current,
}: {
    classes: PortalClass[];
    current: PortalClass | null;
}) {
    if (classes.length === 0) {
        return null;
    }

    return (
        <Menu as="div" className="relative">
            <MenuButton className="flex max-w-[18rem] items-center gap-2 rounded-[4px] bg-portal-soft px-4 py-2.5 text-[15px] font-bold text-portal-accent transition outline-none hover:brightness-97">
                <GraduationCap className="size-4.5 shrink-0" />
                <span className="min-w-0 flex-1 truncate text-left">
                    {current?.label ?? 'Select a class'}
                </span>
                <ChevronsUpDown className="size-4 shrink-0 opacity-60" />
            </MenuButton>
            <MenuItems
                anchor="bottom start"
                className="z-50 mt-1 w-72 rounded-[4px] border border-portal-line bg-white py-1.5 shadow-s3 focus:outline-none"
            >
                {classes.map((c) => (
                    <MenuItem key={c.id}>
                        <Link
                            href={`/portal/classes/${c.id}`}
                            className={cn(
                                'flex items-center gap-2.5 px-3 py-2.5 text-[15px] data-focus:bg-portal-field',
                                c.id === current?.id && 'font-bold',
                            )}
                        >
                            <span
                                className="size-2.5 shrink-0 rounded-full"
                                style={{
                                    backgroundColor: c.color ?? '#159cb0',
                                }}
                            />
                            <span className="min-w-0 flex-1 truncate text-portal-ink">
                                {c.label}
                            </span>
                            <span className="text-xs text-neutral-400">
                                {c.childCount}
                            </span>
                        </Link>
                    </MenuItem>
                ))}
            </MenuItems>
        </Menu>
    );
}

function IconButton({
    label,
    icon: Icon,
    dot,
    href,
}: {
    label: string;
    icon: LucideIcon;
    dot?: boolean;
    href: string;
}) {
    return (
        <Link
            href={href}
            title={label}
            aria-label={label}
            className="relative grid size-10 place-items-center rounded-[4px] bg-neutral-100 text-portal-ink transition hover:bg-neutral-200"
        >
            <Icon className="size-5" strokeWidth={1.75} />
            {dot && (
                <span className="absolute top-1.5 right-1.5 size-2 rounded-full bg-red-500 ring-2 ring-neutral-100" />
            )}
        </Link>
    );
}

export default function PortalLayout({ children }: { children: ReactNode }) {
    const page = usePage<{
        classes?: PortalClass[];
        classroom?: PortalClass;
        auth: Auth;
    }>();
    const { isCurrentUrl } = useCurrentUrl();

    const classes = page.props.classes ?? [];
    const classroom = page.props.classroom ?? null;
    const base = classroom ? `/portal/classes/${classroom.id}` : null;
    const atHome = isCurrentUrl('/portal');
    // Only back-office users can actually use the dashboard.
    const canSwitchToAdmin = Boolean(page.props.auth?.user?.has_admin_access);

    // Mobile tabs: Home plus the current class's tabs (nothing to show without one).
    const mobileTabs: { title: string; href: string; icon: LucideIcon }[] = [
        { title: 'Home', href: '/portal', icon: House },
        ...(base
            ? classTabs.map((t) => ({
                  title: t.title,
                  href: `${base}${t.path}`,
                  icon: t.icon,
              }))
            : []),
    ];

    return (
        <div className="flex min-h-screen flex-col bg-portal-bg">
            {/* Top bar: brand · class switcher · actions */}
            <header className="sticky top-0 z-40 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 md:px-6">
                    <Brand />
                    <span className="hidden h-8 w-px bg-black/10 md:block" />
                    <ClassSwitcher classes={classes} current={classroom} />

                    <div className="ml-auto flex items-center gap-2">
                        {canSwitchToAdmin && (
                            <Link
                                href="/dashboard"
                                className="mr-1 hidden items-center gap-1.5 rounded-[4px] px-3 py-2 text-xs font-medium text-neutral-500 transition hover:bg-neutral-100 hover:text-portal-ink lg:flex"
                            >
                                <ArrowLeftRight className="size-3.5" />
                                Switch to admin
                            </Link>
                        )}
                        <IconButton
                            label="Settings"
                            icon={Settings}
                            href="/portal/settings"
                        />
                    </div>
                </div>

                {/* Desktop: Home pill + the class tabs */}
                <div className="hidden border-t border-portal-line md:block">
                    <div className="mx-auto flex max-w-7xl items-center gap-6 px-6">
                        <Link
                            href="/portal"
                            className={cn(
                                'my-2 flex items-center gap-2 rounded-[4px] px-4 py-2 text-[15px] font-bold transition',
                                atHome
                                    ? 'bg-portal-soft text-portal-accent'
                                    : 'text-neutral-500 hover:bg-neutral-50 hover:text-portal-ink',
                            )}
                        >
                            <House className="size-4" />
                            Home
                        </Link>

                        {base &&
                            classTabs.map(({ title, path, icon: Icon }) => {
                                const href = `${base}${path}`;
                                // "Feed" is the class root, so it must match exactly
                                // or every tab would light up.
                                const active =
                                    path === ''
                                        ? isCurrentUrl(href)
                                        : isCurrentUrl(href) ||
                                          page.url.startsWith(href);

                                return (
                                    <Link
                                        key={title}
                                        href={href}
                                        aria-current={
                                            active ? 'page' : undefined
                                        }
                                        className={cn(
                                            'flex items-center gap-2 border-b-2 py-3.5 text-[15px] font-bold transition-colors',
                                            active
                                                ? 'border-portal-accent text-portal-ink'
                                                : 'border-transparent text-neutral-500 hover:text-portal-ink',
                                        )}
                                    >
                                        <Icon
                                            className={cn(
                                                'size-4.5',
                                                active
                                                    ? 'text-portal-accent'
                                                    : 'text-neutral-400',
                                            )}
                                        />
                                        {title}
                                    </Link>
                                );
                            })}
                    </div>
                </div>
            </header>

            <main className="mx-auto w-full max-w-7xl flex-1 px-4 pb-24 md:px-6 md:pb-10">
                {children}
            </main>

            {/* Mobile: fixed bottom tab bar */}
            <nav
                aria-label="Portal"
                className="fixed inset-x-0 bottom-0 z-40 border-t border-portal-line bg-white pb-[env(safe-area-inset-bottom)] md:hidden"
            >
                <div className="mx-auto flex max-w-md items-stretch">
                    {mobileTabs.map(({ title, href, icon: Icon }) => {
                        const active = isCurrentUrl(href);

                        return (
                            <Link
                                key={title}
                                href={href}
                                aria-current={active ? 'page' : undefined}
                                className={cn(
                                    'flex flex-1 flex-col items-center gap-1 py-2 text-[11px] font-medium transition-colors',
                                    active
                                        ? 'text-portal-accent'
                                        : 'text-neutral-500',
                                )}
                            >
                                <Icon
                                    className="size-6"
                                    strokeWidth={active ? 2 : 1.75}
                                />
                                {title}
                            </Link>
                        );
                    })}
                </div>
            </nav>
        </div>
    );
}
