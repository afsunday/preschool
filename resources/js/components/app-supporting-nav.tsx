import { Link } from '@inertiajs/react';
import { Image, LayoutDashboard } from 'lucide-react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

// Primary admin navigation — GitHub-style underline tabs. Add sections here as
// they're built out.
const defaultItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: LayoutDashboard },
    { title: 'Media', href: '/admin/media', icon: Image },
];

export function AppSupportingNav({
    items = defaultItems,
}: {
    items?: NavItem[];
}) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <div className="border-b border-border/70">
            <div className="mx-auto w-full px-4 md:max-w-7xl">
                <nav
                    aria-label="Primary navigation"
                    className="-mb-px flex items-center gap-1 overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                >
                    {items.map((item) => {
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
                                        'flex items-center gap-2 rounded-md px-2 py-1 text-sm whitespace-nowrap transition-colors group-hover:bg-accent',
                                        active
                                            ? 'font-semibold text-foreground'
                                            : 'text-muted-foreground group-hover:text-foreground',
                                    )}
                                >
                                    {Icon && (
                                        <Icon
                                            className={cn(
                                                'size-4',
                                                active
                                                    ? 'text-foreground'
                                                    : 'text-muted-foreground',
                                            )}
                                        />
                                    )}
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
