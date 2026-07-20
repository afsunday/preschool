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
import { Head, Link, router, useForm } from '@inertiajs/react';
import {
    Archive,
    ArchiveRestore,
    ChevronDown,
    ChevronRight,
    GraduationCap,
    Loader2,
    MoreVertical,
    Pencil,
    Plus,
    X,
} from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import ActionDialog from '@/components/action-dialog';
import { avatarColor } from '@/lib/avatar-color';
import { bannerStyle, DEFAULT_BANNER } from '@/lib/class-banners';
import type { PortalChild, PortalClass } from '@/types/portal';
import { BannerGallery } from './partials/banner-gallery';

interface Teacher {
    id: number;
    name: string;
}

/**
 * A class card, Google Classroom style: the banner with the class name laid over
 * it, and an admin-only ··· menu for editing or archiving.
 *
 * The menu sits outside the Link rather than inside it — nesting a button in an
 * anchor would fire the navigation on every menu click.
 */
function ClassCard({
    item,
    canManage,
    archived,
    onEdit,
    onArchive,
    onRestore,
}: {
    item: PortalClass;
    canManage: boolean;
    archived?: boolean;
    onEdit: (item: PortalClass) => void;
    onArchive: (item: PortalClass) => void;
    onRestore?: (item: PortalClass) => void;
}) {
    return (
        <div
            className={`group relative overflow-hidden rounded-[5px] border border-neutral-300 bg-white transition hover:-translate-y-0.5 hover:shadow-s3 ${archived ? 'opacity-70' : ''}`}
        >
            <Link href={`/portal/classes/${item.id}`} className="block">
                <div style={bannerStyle(item.banner)} className="relative h-34">
                    {/* Scrim keeps the title legible over any banner. */}
                    <div className="absolute inset-0 bg-gradient-to-t from-black/55 via-black/15 to-transparent" />

                    <div className="absolute inset-x-0 bottom-0 p-4">
                        <h3 className="truncate pr-8 text-lg font-bold text-white">
                            {item.name}
                        </h3>
                        <p className="truncate text-sm text-white/80">
                            {[item.grade, item.year]
                                .filter(Boolean)
                                .join(' · ')}
                        </p>
                    </div>
                </div>

                <div className="flex items-center justify-between gap-2 px-4 py-2.5">
                    <span className="truncate text-sm text-neutral-500">
                        {item.teachers.length > 0
                            ? item.teachers.map((t) => t.name).join(', ')
                            : 'Unassigned'}
                    </span>
                    <span className="shrink-0 text-sm font-bold text-neutral-500">
                        {item.childCount}{' '}
                        {item.childCount === 1 ? 'child' : 'children'}
                    </span>
                </div>
            </Link>

            {canManage && (
                <Menu as="div" className="absolute top-2 right-2">
                    <MenuButton
                        aria-label={`Manage ${item.name}`}
                        className="grid size-8 place-items-center rounded-[8px] bg-black/30 text-white backdrop-blur-sm transition hover:bg-black/55"
                    >
                        <MoreVertical className="size-4" />
                    </MenuButton>
                    <MenuItems
                        anchor="bottom end"
                        className="z-50 mt-1 w-44 rounded-[8px] border border-portal-line bg-white py-1 text-sm shadow-s3 focus:outline-none"
                    >
                        <MenuItem>
                            <button
                                type="button"
                                onClick={() => onEdit(item)}
                                className="flex w-full items-center gap-2 px-3 py-2 text-left font-medium text-portal-ink data-focus:bg-portal-field"
                            >
                                <Pencil className="size-4 text-neutral-400" />
                                Edit class
                            </button>
                        </MenuItem>
                        <MenuItem>
                            {archived ? (
                                <button
                                    type="button"
                                    onClick={() => onRestore?.(item)}
                                    className="flex w-full items-center gap-2 px-3 py-2 text-left font-medium text-portal-ink data-focus:bg-portal-field"
                                >
                                    <ArchiveRestore className="size-4 text-neutral-400" />
                                    Restore class
                                </button>
                            ) : (
                                <button
                                    type="button"
                                    onClick={() => onArchive(item)}
                                    className="flex w-full items-center gap-2 px-3 py-2 text-left font-medium text-red-500 data-focus:bg-red-50"
                                >
                                    <Archive className="size-4" />
                                    Archive class
                                </button>
                            )}
                        </MenuItem>
                    </MenuItems>
                </Menu>
            )}
        </div>
    );
}

/**
 * Create or edit a class — admin only. One dialog for both: the fields are
 * identical, and `editing` decides whether it POSTs or PATCHes.
 */
function ClassDialog({
    teachers,
    editing,
    open,
    onClose,
}: {
    teachers: Teacher[];
    editing: PortalClass | null;
    open: boolean;
    onClose: () => void;
}) {
    const [picking, setPicking] = useState(false);
    const form = useForm({
        name: editing?.name ?? '',
        grade: editing?.grade ?? '',
        year: editing?.year ?? '2026/2027',
        banner: editing?.banner ?? DEFAULT_BANNER,
        teacher_ids: editing?.teachers.map((t) => t.id) ?? [],
    });

    const toggleTeacher = (id: number) =>
        form.setData(
            'teacher_ids',
            form.data.teacher_ids.includes(id)
                ? form.data.teacher_ids.filter((t) => t !== id)
                : [...form.data.teacher_ids, id],
        );

    const submit = (e: FormEvent) => {
        e.preventDefault();

        if (editing) {
            form.patch(`/portal/classes/${editing.id}`, { onSuccess: onClose });

            return;
        }

        form.post('/portal/classes', { onSuccess: () => form.reset() });
    };

    return (
        <>
            <Dialog open={open} onClose={onClose} className="relative z-50">
                <DialogBackdrop
                    transition
                    className="fixed inset-0 bg-black/40 duration-150 data-closed:opacity-0"
                />
                <div className="fixed inset-0 overflow-y-auto p-4">
                    <DialogPanel
                        transition
                        className="mx-auto my-auto w-full max-w-[560px] overflow-hidden rounded-[4px] bg-white shadow-s3 duration-150 data-closed:scale-95 data-closed:opacity-0"
                    >
                        <div className="flex items-center justify-between border-b border-portal-line px-5 py-4">
                            <DialogTitle className="text-lg font-bold text-portal-ink">
                                {editing ? `Edit ${editing.name}` : 'New class'}
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
                            {/* Live preview of the cover, exactly as the card renders it. */}
                            <div
                                style={bannerStyle(form.data.banner)}
                                className="relative h-32"
                            >
                                <div className="absolute inset-0 bg-gradient-to-t from-black/55 via-black/15 to-transparent" />
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

                                <button
                                    type="button"
                                    onClick={() => setPicking(true)}
                                    className="absolute top-3 right-3 rounded-[4px] bg-black/40 px-3 py-1.5 text-xs font-bold text-white backdrop-blur-sm transition hover:bg-black/60"
                                >
                                    Change banner
                                </button>
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
                                        className="w-full rounded-[4px] border border-portal-line px-3 py-2.5 text-[15px] outline-none focus:border-portal-accent"
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
                                            className="w-full rounded-[4px] border border-portal-line px-3 py-2.5 text-[15px] outline-none focus:border-portal-accent"
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
                                                form.setData(
                                                    'year',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="2026/2027"
                                            className="w-full rounded-[4px] border border-portal-line px-3 py-2.5 text-[15px] outline-none focus:border-portal-accent"
                                        />
                                        {form.errors.year && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {form.errors.year}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div>
                                    <span className="mb-1 block text-sm font-bold text-portal-ink">
                                        Teachers
                                    </span>
                                    {teachers.length === 0 ? (
                                        <p className="rounded-[4px] border border-portal-line px-3 py-2.5 text-sm text-neutral-400">
                                            No staff yet — add team members
                                            first.
                                        </p>
                                    ) : (
                                        <div className="max-h-40 space-y-0.5 overflow-y-auto rounded-[4px] border border-portal-line p-1">
                                            {teachers.map((t) => {
                                                const on =
                                                    form.data.teacher_ids.includes(
                                                        t.id,
                                                    );

                                                return (
                                                    <label
                                                        key={t.id}
                                                        className="flex cursor-pointer items-center gap-2.5 rounded-[4px] px-2.5 py-2 text-[15px] hover:bg-portal-field"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            checked={on}
                                                            onChange={() =>
                                                                toggleTeacher(
                                                                    t.id,
                                                                )
                                                            }
                                                            className="size-4 accent-portal-accent"
                                                        />
                                                        <span className="text-portal-ink">
                                                            {t.name}
                                                        </span>
                                                    </label>
                                                );
                                            })}
                                        </div>
                                    )}
                                    <p className="mt-1 text-xs text-neutral-400">
                                        A room can have more than one teacher.
                                    </p>
                                </div>

                                {/* The banner is chosen from the preview's own
                                "Change banner" button — a second picker row here
                                only repeated it. */}
                                {form.errors.banner && (
                                    <p className="text-xs text-red-500">
                                        {form.errors.banner}
                                    </p>
                                )}
                            </div>

                            <div className="border-t border-portal-line p-4">
                                <button
                                    type="submit"
                                    disabled={
                                        form.processing ||
                                        form.data.name.trim() === ''
                                    }
                                    className="inline-flex w-full items-center justify-center gap-2 rounded-[4px] bg-portal-accent py-3 text-[15px] font-bold text-white transition hover:brightness-95 disabled:bg-portal-field disabled:text-neutral-400"
                                >
                                    {form.processing && (
                                        <Loader2 className="size-4 animate-spin" />
                                    )}
                                    {editing ? 'Save changes' : 'Create class'}
                                </button>
                            </div>
                        </form>
                    </DialogPanel>
                </div>
            </Dialog>

            {/* A sibling, not a child. Nested inside the form's Dialog the
                gallery inherits its scroll container and lays out against that
                instead of the viewport, which squashes the grid. Its z-60 keeps
                it above, so the form still sits behind while picking. */}
            <BannerGallery
                open={picking}
                value={form.data.banner}
                onPick={(key) => form.setData('banner', key)}
                onClose={() => setPicking(false)}
            />
        </>
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
                    className="inline-flex items-center gap-1.5 rounded-[4px] bg-portal-soft px-4 py-2 text-sm font-bold text-portal-accent transition hover:brightness-97"
                >
                    <Plus className="size-4" />
                    Add a child
                </Link>
            </div>

            {children.length === 0 && (
                <div className="rounded-[4px] border border-dashed border-portal-line px-4 py-8 text-center">
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
                        className="flex items-center gap-3 rounded-[4px] border border-portal-line bg-white px-4 py-3 transition hover:bg-portal-field"
                    >
                        {child.photo ? (
                            <img
                                src={child.photo}
                                alt={child.name}
                                className="size-11 shrink-0 rounded-full object-cover"
                            />
                        ) : (
                            <span
                                className={`grid size-11 shrink-0 place-items-center rounded-full text-base font-bold ${avatarColor(child.name)}`}
                            >
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
    archivedClasses,
    children,
    canManage,
    teachers,
}: {
    classes: PortalClass[];
    archivedClasses: PortalClass[];
    children: PortalChild[] | null;
    canManage: boolean;
    teachers: Teacher[];
}) {
    // null = closed, 'new' = create, a class = edit that one.
    const [dialog, setDialog] = useState<PortalClass | 'new' | null>(null);
    const editing = dialog === 'new' ? null : dialog;

    // One confirm dialog for the whole grid — a card each would mount dozens.
    const [archiving, setArchiving] = useState<PortalClass | null>(null);
    const [archivingBusy, setArchivingBusy] = useState(false);
    const [showArchived, setShowArchived] = useState(false);

    const archive = () => {
        if (!archiving) {
            return;
        }

        router.delete(`/portal/classes/${archiving.id}`, {
            onStart: () => setArchivingBusy(true),
            onFinish: () => {
                setArchivingBusy(false);
                setArchiving(null);
            },
        });
    };

    const restore = (item: PortalClass) => {
        router.patch(
            `/portal/classes/${item.id}/restore`,
            {},
            { preserveScroll: true },
        );
    };

    return (
        <>
            <Head title="Portal" />
            <div className="mb-10 space-y-8 py-3">
                {children && <MyChildren children={children} />}

                <section>
                    <div className="flex items-center justify-between pb-3">
                        <h2 className="text-xl font-bold text-portal-ink">
                            {children ? 'Their classes' : 'My classes'}
                        </h2>
                        {canManage && (
                            <div className="flex items-center gap-2">
                                <Link
                                    href="/admin/team"
                                    className="inline-flex items-center gap-1.5 rounded-[4px] border border-portal-line px-4 py-2 text-sm font-bold text-portal-ink transition hover:bg-neutral-50"
                                >
                                    <GraduationCap className="size-4" />
                                    Team
                                </Link>
                                <button
                                    type="button"
                                    onClick={() => setDialog('new')}
                                    className="inline-flex items-center gap-1.5 rounded-[4px] bg-portal-accent px-4 py-2 text-sm font-bold text-white transition hover:brightness-95"
                                >
                                    <Plus className="size-4" />
                                    New class
                                </button>
                            </div>
                        )}
                    </div>

                    {classes.length === 0 ? (
                        <div className="grid place-items-center rounded-[4px] border border-dashed border-portal-line bg-white px-4 py-14 text-center">
                            <GraduationCap className="size-8 text-neutral-300" />
                            <p className="mt-3 text-[15px] font-bold text-portal-ink">
                                No classes yet
                            </p>
                            <p className="mt-1 max-w-sm text-sm text-neutral-500">
                                {canManage
                                    ? 'Create a class, add children, then hand each family their invite code.'
                                    : 'Once a child is in a room, their parents show up here.'}
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            {classes.map((item) => (
                                <ClassCard
                                    key={item.id}
                                    item={item}
                                    canManage={canManage}
                                    onEdit={setDialog}
                                    onArchive={setArchiving}
                                />
                            ))}
                        </div>
                    )}
                </section>

                {canManage && archivedClasses.length > 0 && (
                    <section>
                        <button
                            type="button"
                            onClick={() => setShowArchived((v) => !v)}
                            className="flex items-center gap-2 pb-3 text-sm font-bold text-neutral-500 transition hover:text-portal-ink"
                        >
                            <Archive className="size-4" />
                            Archived classes ({archivedClasses.length})
                            <ChevronDown
                                className={`size-4 transition-transform ${showArchived ? 'rotate-180' : ''}`}
                            />
                        </button>

                        {showArchived && (
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                {archivedClasses.map((item) => (
                                    <ClassCard
                                        key={item.id}
                                        item={item}
                                        canManage={canManage}
                                        archived
                                        onEdit={setDialog}
                                        onArchive={setArchiving}
                                        onRestore={restore}
                                    />
                                ))}
                            </div>
                        )}
                    </section>
                )}
            </div>

            {canManage && (
                <>
                    <ClassDialog
                        // Remount per target so the form re-seeds from the class
                        // being edited rather than keeping the last one's values.
                        key={dialog === 'new' ? 'new' : (dialog?.id ?? 'none')}
                        teachers={teachers}
                        editing={editing}
                        open={dialog !== null}
                        onClose={() => setDialog(null)}
                    />

                    {/* ActionDialog's `hidden` means *shown* — it stays mounted
                        and slides in, so it is always rendered. */}
                    <ActionDialog
                        hidden={archiving !== null}
                        loading={archivingBusy}
                        btn="red"
                        onClose={() => setArchiving(null)}
                        onAccept={archive}
                    >
                        <span
                            title="icon"
                            className="grid size-12 place-items-center rounded-full bg-red-50 text-red-500"
                        >
                            <Archive className="size-5" />
                        </span>
                        <span title="title">
                            Archive {archiving?.name ?? 'this class'}?
                        </span>
                        <span title="subtitle" className="text-neutral-500">
                            Families keep this year's posts, chats and reports.
                            The class just leaves your list.
                        </span>
                    </ActionDialog>
                </>
            )}
        </>
    );
}
