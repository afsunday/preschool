import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import { Link, router, usePage } from '@inertiajs/react';
import {
    ArrowLeftRight,
    Baby,
    FileText,
    Image,
    LayoutDashboard,
    Library,
    LogOut,
    Mail,
    Send,
    Settings,
    Users,
} from 'lucide-react';
import type { ReactNode } from 'react';
import { Avatar } from '@/components/avatar';
import { UserInfo } from '@/components/user-info';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { cn } from '@/lib/utils';
import { dashboard, logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { NavItem } from '@/types';

type AdminNavItem = NavItem & { permission?: string };

const navItems: AdminNavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: LayoutDashboard },
    {
        title: 'Pages',
        href: '/admin/pages',
        icon: FileText,
        permission: 'content.pages',
    },
    {
        title: 'Resources',
        href: '/admin/materials',
        icon: Library,
        permission: 'content.resources',
    },
    {
        title: 'Media',
        href: '/admin/media',
        icon: Image,
        permission: 'content.media',
    },
    {
        title: 'Messages',
        href: '/admin/messages',
        icon: Mail,
        permission: 'comms.messages',
    },
    {
        title: 'Newsletter',
        href: '/admin/newsletter',
        icon: Send,
        permission: 'comms.newsletter',
    },
    {
        title: 'Team',
        href: '/admin/team',
        icon: Users,
        permission: 'team.staff',
    },
    {
        title: 'Parents',
        href: '/admin/parents',
        icon: Baby,
        permission: 'parents.view',
    },
];

function TopBar() {
    const { auth } = usePage().props;
    const cleanup = useMobileNavigation();

    return (
        <div className="bg-[#24292e] text-white">
            <div className="mx-auto flex h-14 items-center px-4 md:max-w-7xl">
                <Link
                    href={dashboard()}
                    prefetch
                    className="inline-flex items-center rounded-[4px] bg-portal-brand px-2.5 py-1 text-lg font-extrabold tracking-tight text-white"
                >
                    WODI
                </Link>

                <div className="ml-auto flex items-center gap-2">
                    <Link
                        href="/portal"
                        className="hidden items-center gap-1.5 rounded-[4px] border border-white/15 px-3 py-1.5 text-xs font-medium text-white/90 transition hover:bg-white/10 sm:flex"
                    >
                        <ArrowLeftRight className="size-3.5" />
                        Switch to portal
                    </Link>

                    {/* Compact version for phones, where the labelled link is hidden. */}
                    <Link
                        href="/portal"
                        title="Switch to portal"
                        aria-label="Switch to portal"
                        className="grid size-9 place-items-center rounded-[4px] border border-white/15 bg-white/10 text-white/90 transition hover:bg-white/20 sm:hidden"
                    >
                        <ArrowLeftRight className="size-4" />
                    </Link>

                    {auth.user && (
                        <Menu as="div" className="relative">
                            <MenuButton className="flex items-center rounded-full p-1 transition hover:bg-white/10">
                                <Avatar
                                    name={auth.user.name}
                                    src={auth.user.avatar}
                                />
                            </MenuButton>
                            <MenuItems
                                anchor="bottom end"
                                className="z-50 mt-1 w-56 rounded-[4px] border border-black/10 bg-white py-1 text-sm shadow-lg focus:outline-none"
                            >
                                <div className="flex items-center gap-2 px-3 py-2">
                                    <UserInfo user={auth.user} showEmail />
                                </div>
                                <div className="my-1 h-px bg-black/10" />
                                <MenuItem>
                                    <Link
                                        href={edit()}
                                        prefetch
                                        onClick={cleanup}
                                        className="flex items-center gap-2 px-3 py-2 data-focus:bg-neutral-100"
                                    >
                                        <Settings className="size-4" />
                                        Settings
                                    </Link>
                                </MenuItem>
                                <div className="my-1 h-px bg-black/10" />
                                <MenuItem>
                                    <Link
                                        href={logout()}
                                        as="button"
                                        onClick={() => router.flushAll()}
                                        className="flex w-full items-center gap-2 px-3 py-2 text-left data-focus:bg-neutral-100"
                                    >
                                        <LogOut className="size-4" />
                                        Log out
                                    </Link>
                                </MenuItem>
                            </MenuItems>
                        </Menu>
                    )}
                </div>
            </div>
        </div>
    );
}

function PrimaryNav() {
    const { isCurrentUrl } = useCurrentUrl();
    const { auth } = usePage().props;
    const granted = auth.permissions ?? [];

    // Show an item if it needs no permission, or the user has it.
    const visible = navItems.filter(
        (item) => !item.permission || granted.includes(item.permission),
    );

    return (
        <div className="border-b border-border/70">
            <div className="mx-auto w-full px-4 md:max-w-7xl">
                <nav
                    aria-label="Primary navigation"
                    className="-mb-px flex [scrollbar-width:none] items-center gap-1 overflow-x-auto [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden"
                >
                    {visible.map((item) => {
                        const Icon = item.icon;
                        const active = isCurrentUrl(item.href);

                        return (
                            <Link
                                key={item.title}
                                href={item.href}
                                aria-current={active ? 'page' : undefined}
                                className={cn(
                                    'group flex shrink-0 items-center border-b-2 px-1 pt-1 pb-2',
                                    active
                                        ? 'border-[#fd8c73]'
                                        : 'border-transparent',
                                )}
                            >
                                <span
                                    className={cn(
                                        'flex items-center gap-2 rounded-[4px] px-2 py-1 text-sm whitespace-nowrap transition-colors group-hover:bg-accent',
                                        active
                                            ? 'font-semibold text-foreground'
                                            : 'text-muted-foreground group-hover:text-foreground',
                                    )}
                                >
                                    {Icon && <Icon className="size-4" />}
                                    {item.title}
                                </span>
                            </Link>
                        );
                    })}
                </nav>
            </div>
        </div>
    );
}

export default function AppLayout({ children }: { children: ReactNode }) {
    return (
        <div className="flex min-h-screen w-full flex-col">
            <TopBar />
            <PrimaryNav />
            <main className="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4">
                {children}
            </main>
        </div>
    );
}
