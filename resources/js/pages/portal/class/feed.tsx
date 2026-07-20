import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import {
    CalendarDays,
    ChevronLeft,
    ChevronRight,
    Heart,
    Loader2,
    MapPin,
    MessageCircle,
    MoreHorizontal,
    Newspaper,
} from 'lucide-react';
import { useRef, useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import VeeModal from '@/components/vee-modal';
import { avatarColor } from '@/lib/avatar-color';
import { cn } from '@/lib/utils';
import type { PortalClass, PortalPost } from '@/types/portal';
import { PhotoUpload } from '../partials/photo-upload';

function Avatar({ name, className }: { name: string; className?: string }) {
    return (
        <span
            className={cn(
                'grid shrink-0 place-items-center rounded-lg font-bold',
                avatarColor(name),
                className ?? 'size-8 text-sm',
            )}
        >
            {name.charAt(0)}
        </span>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <label className="block">
            <span className="text-xs font-medium text-neutral-600">
                {label}
            </span>
            <div className="mt-1">{children}</div>
            {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
        </label>
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
    const form = useForm({
        type: 'update' as 'update' | 'event',
        body: '',
        photos: [] as string[],
        event_title: '',
        event_at: '',
        event_ends_at: '',
        event_location: '',
    });

    // The modal stays mounted (it slides, never unmounts), so a fresh post needs
    // the photo picker's own preview state remounted — bump this to key it.
    const [uploadKey, setUploadKey] = useState(0);

    const isEvent = form.data.type === 'event';

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post(`/portal/classes/${classroom.id}/posts`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setUploadKey((k) => k + 1);
                onClose();
            },
        });
    };

    return (
        <VeeModal
            hidden={open}
            onClose={onClose}
            sizeClassName="xs:max-w-[560px]"
        >
            <span title="title" className="font-bold text-portal-ink">
                New post
            </span>
            <div title="body" data-slot="body">
                <form onSubmit={submit}>
                    <div className="space-y-4">
                        <div className="flex items-center gap-3">
                            <Avatar name={author} />
                            <p className="text-sm font-bold text-portal-ink">
                                {author}
                            </p>
                        </div>

                        {/* Update vs event */}
                        <div className="inline-flex rounded-[4px] bg-portal-field p-0.5 text-sm font-bold">
                            {(['update', 'event'] as const).map((t) => (
                                <button
                                    key={t}
                                    type="button"
                                    onClick={() => form.setData('type', t)}
                                    className={cn(
                                        'rounded-[4px] px-4 py-1.5 capitalize transition',
                                        form.data.type === t
                                            ? 'bg-white text-portal-accent shadow-s1'
                                            : 'text-neutral-500',
                                    )}
                                >
                                    {t}
                                </button>
                            ))}
                        </div>

                        {isEvent && (
                            <div className="space-y-3 rounded-[4px] border border-portal-line p-3">
                                <Field
                                    label="Event title"
                                    error={form.errors.event_title}
                                >
                                    <input
                                        className="form-control"
                                        value={form.data.event_title}
                                        onChange={(e) =>
                                            form.setData(
                                                'event_title',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="e.g. Swimming lesson"
                                    />
                                </Field>
                                <div className="grid grid-cols-2 gap-2">
                                    <Field
                                        label="Starts"
                                        error={form.errors.event_at}
                                    >
                                        <input
                                            type="datetime-local"
                                            className="form-control"
                                            value={form.data.event_at}
                                            onChange={(e) =>
                                                form.setData(
                                                    'event_at',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                    </Field>
                                    <Field
                                        label="Ends (optional)"
                                        error={form.errors.event_ends_at}
                                    >
                                        <input
                                            type="datetime-local"
                                            className="form-control"
                                            value={form.data.event_ends_at}
                                            onChange={(e) =>
                                                form.setData(
                                                    'event_ends_at',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                    </Field>
                                </div>
                                <Field
                                    label="Location (optional)"
                                    error={form.errors.event_location}
                                >
                                    <input
                                        className="form-control"
                                        value={form.data.event_location}
                                        onChange={(e) =>
                                            form.setData(
                                                'event_location',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="e.g. Community Pool"
                                    />
                                </Field>
                            </div>
                        )}

                        <div>
                            <textarea
                                // Focus lands in the field the dialog exists for.
                                autoFocus={!isEvent}
                                value={form.data.body}
                                onChange={(e) =>
                                    form.setData('body', e.target.value)
                                }
                                rows={isEvent ? 2 : 4}
                                placeholder={
                                    isEvent
                                        ? 'Add details (optional)'
                                        : `What's happening at ${classroom.name}'s class?`
                                }
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
                                key={uploadKey}
                                value={form.data.photos}
                                onChange={(paths) =>
                                    form.setData('photos', paths)
                                }
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        disabled={
                            form.processing ||
                            (isEvent
                                ? form.data.event_title.trim() === '' ||
                                  !form.data.event_at
                                : form.data.body.trim() === '')
                        }
                        className="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-[4px] bg-portal-accent py-3 text-sm font-bold text-white transition hover:brightness-95 disabled:bg-portal-field disabled:text-neutral-400"
                    >
                        {form.processing && (
                            <Loader2 className="size-4 animate-spin" />
                        )}
                        Post
                    </button>
                </form>
            </div>
        </VeeModal>
    );
}

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

/**
 * Post media, full-bleed to the card edges. A lone photo fills the width; two or
 * more become a swipeable slider so the card height stays contained.
 */
function PostImages({ photos }: { photos: string[] }) {
    if (photos.length === 1) {
        return (
            <div className="-mx-5 mt-4 border-y border-portal-line bg-black">
                <img
                    src={photos[0]}
                    alt=""
                    className="max-h-[560px] w-full object-cover"
                />
            </div>
        );
    }

    return <PostSlider photos={photos} />;
}

function PostSlider({ photos }: { photos: string[] }) {
    const trackRef = useRef<HTMLDivElement>(null);
    const [index, setIndex] = useState(0);

    const go = (to: number) => {
        const el = trackRef.current;

        if (!el) {
            return;
        }

        const next = Math.max(0, Math.min(photos.length - 1, to));
        el.scrollTo({ left: next * el.clientWidth, behavior: 'smooth' });
    };

    return (
        <div className="group/slider relative -mx-5 mt-4 border-y border-portal-line bg-black">
            <div
                ref={trackRef}
                onScroll={(e) => {
                    const el = e.currentTarget;
                    setIndex(Math.round(el.scrollLeft / el.clientWidth));
                }}
                className="flex snap-x snap-mandatory [scrollbar-width:none] overflow-x-auto [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden"
            >
                {photos.map((url) => (
                    <img
                        key={url}
                        src={url}
                        alt=""
                        className="h-[300px] w-full shrink-0 snap-center object-cover sm:h-[460px]"
                    />
                ))}
            </div>

            {index > 0 && (
                <button
                    type="button"
                    aria-label="Previous photo"
                    onClick={() => go(index - 1)}
                    className="absolute top-1/2 left-2 grid size-9 -translate-y-1/2 place-items-center rounded-full bg-black/50 text-white transition hover:bg-black/70 md:opacity-0 md:group-hover/slider:opacity-100"
                >
                    <ChevronLeft className="size-5" />
                </button>
            )}
            {index < photos.length - 1 && (
                <button
                    type="button"
                    aria-label="Next photo"
                    onClick={() => go(index + 1)}
                    className="absolute top-1/2 right-2 grid size-9 -translate-y-1/2 place-items-center rounded-full bg-black/50 text-white transition hover:bg-black/70 md:opacity-0 md:group-hover/slider:opacity-100"
                >
                    <ChevronRight className="size-5" />
                </button>
            )}

            <div className="absolute top-2 right-2 rounded-full bg-black/60 px-2 py-0.5 text-xs font-semibold text-white">
                {index + 1} / {photos.length}
            </div>

            <div className="pointer-events-none absolute inset-x-0 bottom-2.5 flex justify-center gap-1.5">
                {photos.map((url, i) => (
                    <span
                        key={url}
                        className={cn(
                            'size-1.5 rounded-full transition',
                            i === index ? 'bg-white' : 'bg-white/50',
                        )}
                    />
                ))}
            </div>
        </div>
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
    const base = `/portal/classes/${classroom.id}/posts/${post.id}`;
    const [showComments, setShowComments] = useState(post.comments.length > 0);
    const comment = useForm({ body: '' });

    const toggleLike = () =>
        router.post(`${base}/like`, {}, { preserveScroll: true });

    const submitComment = (e: FormEvent) => {
        e.preventDefault();
        comment.post(`${base}/comments`, {
            preserveScroll: true,
            onSuccess: () => comment.reset('body'),
        });
    };

    return (
        <article className="overflow-hidden border border-portal-line bg-white md:rounded-[4px]">
            <div className="p-3">
                {/* Author over class, date pinned right — the Class Story header. */}
                <div className="flex items-start gap-3">
                    <Avatar name={post.author} className="size-9 text-base" />
                    <div className="min-w-0 flex-1 leading-tight">
                        <p className="truncate text-sm font-bold text-portal-ink">
                            {post.author}
                        </p>
                        <p className="mt-0.5 truncate text-[13px] font-medium text-neutral-400">
                            {classroom.name}
                        </p>
                    </div>
                    <span className="shrink-0 text-xs font-medium text-neutral-400">
                        {post.createdAt}
                    </span>
                </div>

                {post.type === 'event' && post.event && (
                    <div className="mt-4 flex gap-3.5 rounded-[4px] border border-portal-line bg-portal-soft/50 p-3.5">
                        <div className="grid size-14 shrink-0 place-content-center rounded-[4px] bg-white text-center leading-none shadow-s1">
                            <span className="text-[10px] font-bold tracking-wide text-portal-accent uppercase">
                                {post.event.month}
                            </span>
                            <span className="mt-1 text-xl font-extrabold text-portal-ink">
                                {post.event.day}
                            </span>
                        </div>
                        <div className="min-w-0 self-center">
                            <p className="font-bold text-portal-ink">
                                {post.event.title}
                            </p>
                            <p className="mt-0.5 flex items-center gap-1.5 text-sm text-neutral-600">
                                <CalendarDays className="size-4 shrink-0 text-portal-accent" />
                                {post.event.dateLabel} · {post.event.timeLabel}
                            </p>
                            {post.event.location && (
                                <p className="mt-0.5 flex items-center gap-1.5 text-sm text-neutral-600">
                                    <MapPin className="size-4 shrink-0 text-portal-accent" />
                                    {post.event.location}
                                </p>
                            )}
                        </div>
                    </div>
                )}

                {post.body && (
                    <p className="mt-4 text-sm leading-relaxed whitespace-pre-wrap text-portal-ink">
                        {post.body}
                    </p>
                )}

                {post.photos.length > 0 && <PostImages photos={post.photos} />}
            </div>

            <div className="flex items-center gap-1 border-t border-portal-line px-2.5 py-1.5">
                <button
                    type="button"
                    onClick={toggleLike}
                    className={cn(
                        'inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-semibold transition',
                        post.likedByMe
                            ? 'text-red-500 hover:bg-red-50'
                            : 'text-neutral-600 hover:bg-portal-field',
                    )}
                >
                    <Heart
                        className={cn(
                            'size-[18px]',
                            post.likedByMe && 'fill-current',
                        )}
                    />
                    {post.likesCount > 0 ? post.likesCount : 'Like'}
                </button>
                <button
                    type="button"
                    onClick={() => setShowComments((v) => !v)}
                    className="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-semibold text-neutral-600 transition hover:bg-portal-field"
                >
                    <MessageCircle className="size-[18px]" />
                    {post.comments.length > 0
                        ? post.comments.length
                        : 'Comment'}
                </button>

                {canPost && (
                    <Menu as="div" className="relative ml-auto">
                        <MenuButton className="grid size-9 place-items-center rounded-full text-neutral-400 transition hover:bg-portal-field hover:text-portal-ink">
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
                                        router.delete(`${base}`, {
                                            preserveScroll: true,
                                        })
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

            {showComments && (
                <div className="space-y-2 border-t border-portal-line bg-neutral-50/60 px-5 py-3.5">
                    {post.comments.length === 0 && (
                        <p className="text-sm text-neutral-400">
                            No comments yet — be the first.
                        </p>
                    )}

                    {post.comments.map((c) => (
                        <div
                            key={c.id}
                            className="group flex items-start gap-2"
                        >
                            <Avatar
                                name={c.author}
                                className="size-7 text-[11px]"
                            />
                            <div className="max-w-[85%] min-w-0 rounded-2xl rounded-tl-sm border border-portal-line bg-white px-3 py-1.5">
                                <p className="text-[12px] font-semibold text-portal-accent">
                                    {c.author}
                                </p>
                                <p className="text-sm whitespace-pre-wrap text-portal-ink">
                                    {c.body}
                                </p>
                                <div className="mt-1 flex items-center justify-end gap-2 text-[10px] text-neutral-400">
                                    {(c.mine || canPost) && (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.delete(
                                                    `${base}/comments/${c.id}`,
                                                    { preserveScroll: true },
                                                )
                                            }
                                            className="font-semibold transition hover:text-red-500 sm:opacity-0 sm:group-hover:opacity-100"
                                        >
                                            Delete
                                        </button>
                                    )}
                                    <span>{c.at}</span>
                                </div>
                            </div>
                        </div>
                    ))}

                    <form
                        onSubmit={submitComment}
                        className="flex items-center gap-2 pt-1"
                    >
                        <input
                            value={comment.data.body}
                            onChange={(e) =>
                                comment.setData('body', e.target.value)
                            }
                            placeholder="Write a comment…"
                            className="flex-1 rounded-[4px] border border-portal-line bg-white px-3 py-2 text-sm outline-none focus:border-portal-accent"
                        />
                        <button
                            type="submit"
                            disabled={
                                comment.processing ||
                                comment.data.body.trim() === ''
                            }
                            className="rounded-[4px] bg-portal-accent px-4 py-2 text-sm font-bold text-white transition hover:brightness-95 disabled:opacity-50"
                        >
                            Post
                        </button>
                    </form>
                </div>
            )}
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
            <div className="mx-auto grid w-full max-w-[800px] gap-5 lg:grid-cols-[1fr_300px]">
                <div className="mb-16 space-y-2 sm:space-y-4">
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
                                <dt className="text-neutral-500">Teachers</dt>
                                <dd className="font-bold text-portal-ink">
                                    {classroom.teachers
                                        .map((t) => t.name)
                                        .join(', ') || '—'}
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
                            Roster tab, then link themselves.
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

ClassFeed.layout = { mainClassName: 'px-0 md:px-6' };
