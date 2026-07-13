import { Link } from '@inertiajs/react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

// Supporting (secondary) navigation. Replace the placeholder hrefs ('#') with
// real routes as those sections are built out.
const defaultItems: NavItem[] = [
    { title: 'All', href: dashboard() },
    { title: 'Parent Tips', href: '#' },
    { title: 'Learning Activities', href: '#' },
    { title: 'Educational Videos', href: '#' },
    { title: 'Health & Wellness', href: '#' },
    { title: 'Arts & Craft', href: '#' },
    { title: 'School Readiness', href: '#' },
    { title: 'Nutrition', href: '#' },
    { title: 'Safety', href: '#' },
    { title: 'Development', href: '#' },
];

export function AppSupportingNav({
    items = defaultItems,
}: {
    items?: NavItem[];
}) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <div className="border-b border-sidebar-border/70">
            <div className="mx-auto w-full px-4 md:max-w-7xl">
                <nav
                    aria-label="Supporting navigation"
                    className="flex items-center gap-1 overflow-x-auto py-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                >
                    {items.map((item) => (
                        <Link
                            key={item.title}
                            href={item.href}
                            className={cn(
                                'shrink-0 rounded-full px-4 py-1.5 text-sm font-medium whitespace-nowrap text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground',
                                isCurrentUrl(item.href) &&
                                    'bg-neutral-900 text-white hover:bg-neutral-900 hover:text-white dark:bg-white dark:text-neutral-900 dark:hover:bg-white dark:hover:text-neutral-900',
                            )}
                        >
                            {item.title}
                        </Link>
                    ))}
                </nav>
            </div>
        </div>
    );
}
