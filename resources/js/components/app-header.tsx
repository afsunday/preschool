import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import { Link, router, usePage } from '@inertiajs/react';
import { LogOut, Settings } from 'lucide-react';
import { AppSupportingNav } from '@/components/app-supporting-nav';
import { Avatar } from '@/components/avatar';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { dashboard, logout } from '@/routes';
import { edit } from '@/routes/profile';

export function AppHeader() {
    const { auth } = usePage().props;
    const cleanup = useMobileNavigation();

    return (
        <>
            <div className="bg-[#24292e] text-white">
                <div className="mx-auto flex h-14 items-center px-4 md:max-w-7xl">
                    <Link
                        href={dashboard()}
                        prefetch
                        className="flex items-center space-x-2"
                    >
                        <span className="text-sm font-semibold">
                            {usePage().props.name}
                        </span>
                    </Link>

                    <div className="ml-auto flex items-center">
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
                                        <UserInfo
                                            user={auth.user}
                                            showEmail
                                        />
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
            <AppSupportingNav />
        </>
    );
}
