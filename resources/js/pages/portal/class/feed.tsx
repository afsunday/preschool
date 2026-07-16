import {
    Dialog,
    DialogBackdrop,
    DialogPanel,
    DialogTitle,
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
} from '@headlessui/react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import {
    CalendarDays,
    Heart,
    ImageIcon,
    Loader2,
    MessageCircle,
    MoreHorizontal,
    Newspaper,
    Paperclip,
    X,
} from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { cn } from '@/lib/utils';
import type { PortalClass, PortalPost } from '@/types/portal';
import { PhotoUpload } from '../partials/photo-upload';

function Avatar({ name, className }: { name: string; className?: string }) {
    return (
        <span
            className={cn(
                'grid shrink-0 place-items-center rounded-full bg-portal-soft font-bold text-portal-accent',
                className ?? 'size-10 text-sm',
            )}
        >
            {name.charAt(0)}
        </span>
    );
}

/**
 * The New post dialog — the composer proper. Clicking the quiet field on the feed
 * opens this rather than expanding in place, so writing a post gets the whole
 * screen's attention.
 */
function NewPostDialog({
    classroom,
    author,
    open,
    onClose,
}: {
    classroom: PortalClass;
    author: string;
    open: boolean;
    onClose: () => void;
}) {
    const form = useForm<{ body: string; photos: string[] }>({
        body: '',
        photos: [],
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post(`/portal/classes/${classroom.id}/posts`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onClose();
            },
        });
    };

    return (
        <Dialog open={open} onClose={onClose} className="relative z-50">
            <DialogBackdrop
                transition
                className="fixed inset-0 bg-black/40 duration-150 data-closed:opacity-0"
            />
            <div className="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel
                    transition
                    className="w-full max-w-[560px] overflow-hidden rounded-[4px] bg-white shadow-s3 duration-150 data-closed:scale-95 data-closed:opacity-0"
                >
                    <div className="flex items-center justify-between border-b border-portal-line px-5 py-4">
                        <DialogTitle className="text-lg font-bold text-portal-ink">
                            New post
                        </DialogTitle>
                        <button
                            type="button"
                            onClick={onClose}
                            aria-label="Close"
                            className="grid size-9 place-items-center rounded-[4px] bg-portal-field text-portal-ink transition hover:bg-neutral-200"
                        >
                            <X className="size-4.5" />
                        </button>
                    </div>

                    <form onSubmit={submit}>
                        <div className="space-y-4 p-5">
                            <div className="flex items-center gap-3">
                                <Avatar name={author} />
                                <p className="text-sm font-bold text-portal-ink">
                                    {author}
                                </p>
                            </div>

                            <div>
                                <textarea
                                    // Focus lands in the field the dialog exists for.
                                    autoFocus
                                    value={form.data.body}
                                    onChange={(e) =>
                                        form.setData('body', e.target.value)
                                    }
                                    rows={4}
                                    placeholder={`What's happening at ${classroom.name}'s class?`}
                                    className="w-full resize-none rounded-[4px] border border-portal-line px-4 py-3 text-sm text-portal-ink outline-none placeholder:text-neutral-500 focus:border-portal-accent"
                                />
                                {form.errors.body && (
                                    <p className="mt-1 text-xs text-red-500">
                                        {form.errors.body}
                                    </p>
                                )}
                            </div>

                            <div>
                                <p className="mb-2 text-sm font-bold text-portal-ink">
                                    Photos
                                </p>
                                {/* Uploads as they're chosen, so posting is
                                    instant once the teacher stops typing. */}
                                <PhotoUpload
                                    value={form.data.photos}
                                    onChange={(paths) =>
                                        form.setData('photos', paths)
                                    }
                                />
                            </div>
                        </div>

                        <div className="border-t border-portal-line p-4">
                            <button
                                type="submit"
                                disabled={
                                    form.processing ||
                                    form.data.body.trim() === ''
                                }
                                className="inline-flex w-full items-center justify-center gap-2 rounded-[4px] bg-portal-accent py-3 text-sm font-bold text-white transition hover:brightness-95 disabled:bg-portal-field disabled:text-neutral-400"
                            >
                                {form.processing && (
                                    <Loader2 className="size-4 animate-spin" />
                                )}
                                Post
                            </button>
                        </div>
                    </form>
                </DialogPanel>
            </div>
        </Dialog>
    );
}

/**
 * The feed's quiet trigger: it looks like a field, but clicking anywhere on it
 * opens the New post dialog.
 *
 * Only staff broadcast — a post reaches every guardian of every child in the room.
 */
function Composer({
    classroom,
    author,
}: {
    classroom: PortalClass;
    author: string;
}) {
    const [open, setOpen] = useState(false);

    return (
        <>
            <div className="rounded-[4px] border border-portal-line bg-white p-4">
                <div className="flex items-center gap-3">
                    <Avatar name={author} className="size-11 text-sm" />
                    <button
                        type="button"
                        onClick={() => setOpen(true)}
                        className="flex-1 rounded-[4px] bg-portal-field px-4 py-3 text-left text-sm text-neutral-500 transition hover:bg-neutral-200"
                    >
                        What's happening at {classroom.name}'s class?
                    </button>
                </div>

                {/* Three equal-width pills filling the row, as in Class Story. */}
                <div className="mt-4 flex items-center gap-3 border-t border-portal-line pt-4">
                    {(
                        [
                            [
                                'Photo/Video',
                                ImageIcon,
                                'bg-[#eef4ff] text-[#3b6fd4]',
                            ],
                            [
                                'Event',
                                CalendarDays,
                                'bg-[#eafaf1] text-[#2e9e63]',
                            ],
                            ['File', Paperclip, 'bg-[#eaf7fd] text-[#3a97c9]'],
                        ] as const
                    ).map(([label, Icon, tone]) => (
                        <button
                            key={label}
                            type="button"
                            onClick={() => setOpen(true)}
                            className={cn(
                                'inline-flex flex-1 items-center justify-center gap-2 rounded-[4px] py-3 text-sm font-bold transition hover:brightness-97',
                                tone,
                            )}
                        >
                            <Icon className="size-4.5" />
                            {label}
                        </button>
                    ))}
                </div>
            </div>

            <NewPostDialog
                classroom={classroom}
                author={author}
                open={open}
                onClose={() => setOpen(false)}
            />
        </>
    );
}

function PostCard({
    post,
    classroom,
    canPost,
}: {
    post: PortalPost;
    classroom: PortalClass;
    canPost: boolean;
}) {
    return (
        <article className="overflow-hidden rounded-[4px] border border-portal-line bg-white">
            <div className="p-5">
                {/* Author over class, date pinned right — the Class Story header. */}
                <div className="flex items-start gap-3">
                    <Avatar name={post.author} className="size-11 text-base" />
                    <div className="min-w-0 flex-1">
                        <p className="truncate text-base font-bold text-portal-ink">
                            {post.author}
                        </p>
                        <p className="truncate text-base font-bold text-neutral-500">
                            {classroom.name}
                        </p>
                    </div>
                    <span className="shrink-0 text-sm font-medium text-neutral-500">
                        {post.createdAt}
                    </span>
                </div>

                <p className="mt-4 text-[15px] leading-relaxed whitespace-pre-wrap text-portal-ink">
                    {post.body}
                </p>

                {post.photos.length > 0 && (
                    <div className="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                        {post.photos.map((url) => (
                            <img
                                key={url}
                                src={url}
                                alt=""
                                className="aspect-4/3 w-full rounded-[4px] object-cover"
                            />
                        ))}
                    </div>
                )}
            </div>

            <div className="flex items-center gap-3 border-t border-portal-line px-5 py-4">
                <button
                    type="button"
                    className="inline-flex items-center gap-2 rounded-[4px] border border-portal-line px-5 py-2.5 text-[15px] font-bold text-portal-ink transition hover:bg-portal-field"
                >
                    <Heart className="size-4.5" />
                    Like
                </button>
                <button
                    type="button"
                    className="inline-flex items-center gap-2 rounded-[4px] border border-portal-line px-5 py-2.5 text-[15px] font-bold text-portal-ink transition hover:bg-portal-field"
                >
                    <MessageCircle className="size-4.5" />
                    Comment
                </button>

                {canPost && (
                    <Menu as="div" className="relative ml-auto">
                        <MenuButton className="grid size-9 place-items-center rounded-[4px] text-neutral-400 transition hover:bg-portal-field hover:text-portal-ink">
                            <MoreHorizontal className="size-5" />
                        </MenuButton>
                        <MenuItems
                            anchor="bottom end"
                            className="z-50 mt-1 w-40 rounded-[4px] border border-portal-line bg-white py-1 text-sm shadow-s3 focus:outline-none"
                        >
                            <MenuItem>
                                <button
                                    type="button"
                                    onClick={() =>
                                        router.delete(
                                            `/portal/classes/${classroom.id}/posts/${post.id}`,
                                            { preserveScroll: true },
                                        )
                                    }
                                    className="block w-full px-3 py-2 text-left font-medium text-red-500 data-focus:bg-red-50"
                                >
                                    Delete post
                                </button>
                            </MenuItem>
                        </MenuItems>
                    </Menu>
                )}
            </div>
        </article>
    );
}

export default function ClassFeed({
    classroom,
    posts,
    canPost,
}: {
    classroom: PortalClass;
    posts: PortalPost[];
    canPost: boolean;
}) {
    // The composer posts as the signed-in user, not as the room's teacher.
    const { auth } = usePage().props;
    const author = auth.user?.name ?? 'You';

    return (
        <>
            <Head title={`${classroom.name} · Feed`} />
            <div className="grid gap-5 py-5 lg:grid-cols-[1fr_320px]">
                <div className="space-y-4">
                    {canPost && (
                        <Composer classroom={classroom} author={author} />
                    )}

                    {posts.length === 0 ? (
                        <div className="grid place-items-center rounded-[4px] border border-dashed border-portal-line bg-white px-4 py-14 text-center">
                            <Newspaper className="size-8 text-neutral-300" />
                            <p className="mt-3 text-sm font-medium text-portal-ink">
                                Nothing posted yet
                            </p>
                            <p className="mt-1 text-xs text-neutral-500">
                                {canPost
                                    ? 'Share a photo or an update — every parent in this room will see it.'
                                    : 'Updates from this room will appear here.'}
                            </p>
                        </div>
                    ) : (
                        posts.map((post) => (
                            <PostCard
                                key={post.id}
                                post={post}
                                classroom={classroom}
                                canPost={canPost}
                            />
                        ))
                    )}
                </div>

                {/* Right rail — ClassDojo stacks distinct cards rather than one panel. */}
                <aside className="hidden space-y-4 lg:block">
                    <div className="rounded-[4px] border border-portal-line bg-white p-5">
                        <h3 className="text-xl font-bold text-portal-ink">
                            This class
                        </h3>
                        <dl className="mt-4 space-y-3 text-[15px]">
                            <div className="flex justify-between">
                                <dt className="text-neutral-500">Teacher</dt>
                                <dd className="font-bold text-portal-ink">
                                    {classroom.teacher ?? '—'}
                                </dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-neutral-500">Year</dt>
                                <dd className="font-bold text-portal-ink">
                                    {classroom.year}
                                </dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-neutral-500">Students</dt>
                                <dd className="font-bold text-portal-ink">
                                    {classroom.childCount}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {/* Invite families: the roster is where the codes live. */}
                    <div className="rounded-[4px] border border-portal-line bg-white p-5 text-center">
                        <h3 className="text-xl font-bold text-portal-ink">
                            Invite families
                        </h3>
                        <span className="mx-auto mt-4 grid size-20 place-items-center rounded-[4px] bg-portal-soft text-4xl">
                            👋
                        </span>
                        <p className="mt-4 text-[15px] leading-relaxed text-neutral-600">
                            Parents get their child's invite code from the
                            Students tab, then link themselves.
                        </p>
                        <Link
                            href={`/portal/classes/${classroom.id}/students`}
                            className="mt-4 inline-block w-full rounded-[4px] bg-portal-accent px-4 py-3 text-[15px] font-bold text-white transition hover:brightness-95"
                        >
                            Invite families
                        </Link>
                    </div>
                </aside>
            </div>
        </>
    );
}
