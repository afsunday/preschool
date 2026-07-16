import {
    Dialog,
    DialogBackdrop,
    DialogPanel,
    DialogTitle,
} from '@headlessui/react';
import { Head, Link, useForm } from '@inertiajs/react';
import { ChevronRight, GraduationCap, Loader2, Plus, X } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { createHttpMediaApi, MediaPicker } from '@/cms/media';
import type { MediaItem } from '@/cms/media';
import type { PortalChild, PortalClass } from '@/types/portal';

const mediaApi = createHttpMediaApi('/admin/media/items');

interface Teacher {
    id: number;
    name: string;
}

/**
 * A class card, Google Classroom style: a banner image with the class name laid
 * over it. Falls back to a flat header when no banner has been chosen — the
 * daycare uploads its own photos, we ship no stock art.
 */
function ClassCard({ item }: { item: PortalClass }) {
    return (
        <Link
            href={`/portal/classes/${item.id}`}
            className="group overflow-hidden rounded-[16px] border border-portal-line bg-white transition hover:-translate-y-0.5 hover:shadow-s3"
        >
            <div className="relative h-32 bg-portal-ink">
                {item.banner && (
                    <img
                        src={item.banner}
                        alt=""
                        className="absolute inset-0 h-full w-full object-cover"
                    />
                )}
                {/* Scrim keeps the title legible over any photo. */}
                <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/25 to-transparent" />

                <div className="absolute inset-x-0 bottom-0 p-4">
                    <h3 className="truncate text-lg font-bold text-white">
                        {item.name}
                    </h3>
                    <p className="truncate text-sm text-white/80">
                        {[item.grade, item.year].filter(Boolean).join(' · ')}
                    </p>
                </div>
            </div>

            <div className="flex items-center justify-between p-4">
                <span className="text-sm text-neutral-500">
                    {item.teacher ?? 'Unassigned'}
                </span>
                <span className="rounded-full bg-portal-field px-2.5 py-1 text-xs font-bold text-neutral-600">
                    {item.childCount}{' '}
                    {item.childCount === 1 ? 'child' : 'children'}
                </span>
            </div>
        </Link>
    );
}

/** Create a class — admin only. The banner is picked from the media library. */
function NewClassDialog({
    teachers,
    open,
    onClose,
}: {
    teachers: Teacher[];
    open: boolean;
    onClose: () => void;
}) {
    const [banner, setBanner] = useState<MediaItem | null>(null);
    const form = useForm({
        name: '',
        grade: '',
        year: '2026/2027',
        banner_media_id: null as number | null,
        teacher_id: '',
    });

    const pickBanner = (item: MediaItem | null) => {
        setBanner(item);
        form.setData('banner_media_id', item?.id ?? null);
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post('/portal/classes', {
            onSuccess: () => {
                form.reset();
                setBanner(null);
            },
        });
    };

    return (
        <Dialog open={open} onClose={onClose} className="relative z-50">
            <DialogBackdrop
                transition
                className="fixed inset-0 bg-black/40 duration-150 data-closed:opacity-0"
            />
            <div className="fixed inset-0 overflow-y-auto p-4">
                <DialogPanel
                    transition
                    className="mx-auto my-auto w-full max-w-[560px] overflow-hidden rounded-[16px] bg-white shadow-s3 duration-150 data-closed:scale-95 data-closed:opacity-0"
                >
                    <div className="flex items-center justify-between border-b border-portal-line px-5 py-4">
                        <DialogTitle className="text-lg font-bold text-portal-ink">
                            New class
                        </DialogTitle>
                        <button
                            type="button"
                            onClick={onClose}
                            aria-label="Close"
                            className="grid size-9 place-items-center rounded-full bg-portal-field text-portal-ink transition hover:bg-neutral-200"
                        >
                            <X className="size-4.5" />
                        </button>
                    </div>

                    <form onSubmit={submit}>
                        {/* Live preview of the cover, exactly as the card renders it. */}
                        <div className="relative h-32 bg-portal-ink">
                            {banner && (
                                <img
                                    src={banner.url}
                                    alt=""
                                    className="absolute inset-0 h-full w-full object-cover"
                                />
                            )}
                            <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/25 to-transparent" />
                            <div className="absolute inset-x-0 bottom-0 p-4">
                                <p className="truncate text-lg font-bold text-white">
                                    {form.data.name || 'New class'}
                                </p>
                                <p className="truncate text-sm text-white/80">
                                    {[form.data.grade, form.data.year]
                                        .filter(Boolean)
                                        .join(' · ')}
                                </p>
                            </div>
                        </div>

                        <div className="space-y-4 p-5">
                            <div>
                                <label
                                    htmlFor="name"
                                    className="mb-1 block text-sm font-bold text-portal-ink"
                                >
                                    Class name
                                </label>
                                <input
                                    id="name"
                                    value={form.data.name}
                                    onChange={(e) =>
                                        form.setData('name', e.target.value)
                                    }
                                    placeholder="Mr James"
                                    className="w-full rounded-[10px] border border-portal-line px-3 py-2.5 text-[15px] outline-none focus:border-portal-accent"
                                />
                                {form.errors.name && (
                                    <p className="mt-1 text-xs text-red-500">
                                        {form.errors.name}
                                    </p>
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        htmlFor="grade"
                                        className="mb-1 block text-sm font-bold text-portal-ink"
                                    >
                                        Grade
                                    </label>
                                    <input
                                        id="grade"
                                        value={form.data.grade}
                                        onChange={(e) =>
                                            form.setData(
                                                'grade',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Grade 1"
                                        className="w-full rounded-[10px] border border-portal-line px-3 py-2.5 text-[15px] outline-none focus:border-portal-accent"
                                    />
                                </div>
                                <div>
                                    <label
                                        htmlFor="year"
                                        className="mb-1 block text-sm font-bold text-portal-ink"
                                    >
                                        Year
                                    </label>
                                    <input
                                        id="year"
                                        value={form.data.year}
                                        onChange={(e) =>
                                            form.setData('year', e.target.value)
                                        }
                                        placeholder="2026/2027"
                                        className="w-full rounded-[10px] border border-portal-line px-3 py-2.5 text-[15px] outline-none focus:border-portal-accent"
                                    />
                                    {form.errors.year && (
                                        <p className="mt-1 text-xs text-red-500">
                                            {form.errors.year}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label
                                    htmlFor="teacher"
                                    className="mb-1 block text-sm font-bold text-portal-ink"
                                >
                                    Teacher
                                </label>
                                <select
                                    id="teacher"
                                    value={form.data.teacher_id}
                                    onChange={(e) =>
                                        form.setData(
                                            'teacher_id',
                                            e.target.value,
                                        )
                                    }
                                    className="w-full rounded-[10px] border border-portal-line px-3 py-2.5 text-[15px] outline-none focus:border-portal-accent"
                                >
                                    <option value="">— Unassigned —</option>
                                    {teachers.map((t) => (
                                        <option key={t.id} value={t.id}>
                                            {t.name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {/* The cover comes from the media library — the same
                                picker the page builder uses. */}
                            <div>
                                <p className="mb-2 text-sm font-bold text-portal-ink">
                                    Banner
                                </p>
                                <MediaPicker
                                    api={mediaApi}
                                    value={banner}
                                    kind="image"
                                    onChange={pickBanner}
                                />
                                <p className="mt-1.5 text-xs text-neutral-500">
                                    A wide photo works best. Leave it empty for
                                    a plain header.
                                </p>
                            </div>
                        </div>

                        <div className="border-t border-portal-line p-4">
                            <button
                                type="submit"
                                disabled={
                                    form.processing ||
                                    form.data.name.trim() === ''
                                }
                                className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-portal-accent py-3 text-[15px] font-bold text-white transition hover:brightness-95 disabled:bg-portal-field disabled:text-neutral-400"
                            >
                                {form.processing && (
                                    <Loader2 className="size-4 animate-spin" />
                                )}
                                Create class
                            </button>
                        </div>
                    </form>
                </DialogPanel>
            </div>
        </Dialog>
    );
}

/** A parent's own children — the fastest route to the kid they care about. */
function MyChildren({ children }: { children: PortalChild[] }) {
    return (
        <section>
            <div className="flex items-center justify-between pb-3">
                <h2 className="text-xl font-bold text-portal-ink">
                    My children
                </h2>
                {/* One code per child — this is how a parent adds a second kid. */}
                <Link
                    href="/portal/join"
                    className="inline-flex items-center gap-1.5 rounded-full bg-portal-soft px-4 py-2 text-sm font-bold text-portal-accent transition hover:brightness-97"
                >
                    <Plus className="size-4" />
                    Add a child
                </Link>
            </div>

            {children.length === 0 && (
                <div className="rounded-[16px] border border-dashed border-portal-line px-4 py-8 text-center">
                    <p className="text-[15px] font-bold text-portal-ink">
                        No children linked yet
                    </p>
                    <p className="mt-1 text-sm text-neutral-500">
                        Enter the invite code the school gave you.
                    </p>
                </div>
            )}

            <div className="space-y-2">
                {children.map((child) => (
                    <Link
                        key={child.id}
                        href={`/portal/classes/${child.classroomId}/today`}
                        className="flex items-center gap-3 rounded-[12px] border border-portal-line bg-white px-4 py-3 transition hover:bg-portal-field"
                    >
                        {child.photo ? (
                            <img
                                src={child.photo}
                                alt={child.name}
                                className="size-11 shrink-0 rounded-full object-cover"
                            />
                        ) : (
                            <span className="grid size-11 shrink-0 place-items-center rounded-full bg-portal-soft text-base font-bold text-portal-accent">
                                {child.name.charAt(0)}
                            </span>
                        )}
                        <span className="min-w-0 flex-1">
                            <span className="block truncate text-[15px] font-bold text-portal-ink">
                                {child.name}
                            </span>
                            <span className="block truncate text-sm text-neutral-500">
                                {child.classroom}
                            </span>
                        </span>
                        <ChevronRight className="size-5 shrink-0 text-neutral-300" />
                    </Link>
                ))}
            </div>
        </section>
    );
}

export default function PortalHome({
    classes,
    children,
    canCreate,
    teachers,
}: {
    classes: PortalClass[];
    children: PortalChild[] | null;
    canCreate: boolean;
    teachers: Teacher[];
}) {
    const [creating, setCreating] = useState(false);

    return (
        <>
            <Head title="Portal" />
            <div className="space-y-8 py-6">
                {children && <MyChildren children={children} />}

                <section>
                    <div className="flex items-center justify-between pb-3">
                        <h2 className="text-xl font-bold text-portal-ink">
                            {children ? 'Their classes' : 'My classes'}
                        </h2>
                        {canCreate && (
                            <button
                                type="button"
                                onClick={() => setCreating(true)}
                                className="inline-flex items-center gap-1.5 rounded-full bg-portal-accent px-4 py-2 text-sm font-bold text-white transition hover:brightness-95"
                            >
                                <Plus className="size-4" />
                                New class
                            </button>
                        )}
                    </div>

                    {classes.length === 0 ? (
                        <div className="grid place-items-center rounded-[16px] border border-dashed border-portal-line bg-white px-4 py-14 text-center">
                            <GraduationCap className="size-8 text-neutral-300" />
                            <p className="mt-3 text-[15px] font-bold text-portal-ink">
                                No classes yet
                            </p>
                            <p className="mt-1 max-w-sm text-sm text-neutral-500">
                                {canCreate
                                    ? 'Create a class, add children, then hand each family their invite code.'
                                    : 'Once a child is in a room, their parents show up here.'}
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {classes.map((item) => (
                                <ClassCard key={item.id} item={item} />
                            ))}
                        </div>
                    )}
                </section>
            </div>

            {canCreate && (
                <NewClassDialog
                    teachers={teachers}
                    open={creating}
                    onClose={() => setCreating(false)}
                />
            )}
        </>
    );
}
