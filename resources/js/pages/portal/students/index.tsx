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
import { Head, useForm } from '@inertiajs/react';
import {
    ArrowRightLeft,
    Check,
    ChevronDown,
    Clock,
    Copy,
    Download,
    FileText,
    Loader2,
    MoreVertical,
    Pencil,
    Plus,
    Search,
    Users,
    X,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import { cn } from '@/lib/utils';
import type { DirectoryClass, DirectoryStudent } from '@/types/portal';

function Avatar({ name, src }: { name: string; src?: string | null }) {
    return src ? (
        <img
            src={src}
            alt={name}
            className="size-11 shrink-0 rounded-full object-cover"
        />
    ) : (
        <span className="grid size-11 shrink-0 place-items-center rounded-full bg-portal-soft text-base font-bold text-portal-accent">
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
            <span className="mb-1 block text-sm font-bold text-portal-ink">
                {label}
            </span>
            {children}
            {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
        </label>
    );
}

const inputClass =
    'w-full rounded-[4px] border border-portal-line px-3 py-2.5 text-[15px] outline-none focus:border-portal-accent';

/** Add or edit a student — the fields are identical; `editing` decides the verb. */
function StudentDialog({
    editing,
    classes,
    open,
    onClose,
}: {
    editing: DirectoryStudent | null;
    classes: DirectoryClass[];
    open: boolean;
    onClose: () => void;
}) {
    const form = useForm({
        first_name: editing?.firstName ?? '',
        last_name: editing?.lastName ?? '',
        dob: editing?.dob ?? '',
        notes: editing?.notes ?? '',
        classroom_id: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();

        if (editing) {
            form.patch(`/portal/students/${editing.id}`, {
                onSuccess: onClose,
            });

            return;
        }

        form.post('/portal/students', {
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
            <div className="fixed inset-0 overflow-y-auto p-4">
                <DialogPanel
                    transition
                    className="mx-auto my-auto w-full max-w-[520px] overflow-hidden rounded-[4px] bg-white shadow-s3 duration-150 data-closed:scale-95 data-closed:opacity-0"
                >
                    <div className="flex items-center justify-between border-b border-portal-line px-5 py-4">
                        <DialogTitle className="text-lg font-bold text-portal-ink">
                            {editing
                                ? `Edit ${editing.firstName}`
                                : 'Add student'}
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

                    <form onSubmit={submit} className="space-y-4 p-5">
                        <div className="grid grid-cols-2 gap-4">
                            <Field
                                label="First name"
                                error={form.errors.first_name}
                            >
                                <input
                                    className={inputClass}
                                    value={form.data.first_name}
                                    onChange={(e) =>
                                        form.setData(
                                            'first_name',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Ada"
                                />
                            </Field>
                            <Field
                                label="Last name"
                                error={form.errors.last_name}
                            >
                                <input
                                    className={inputClass}
                                    value={form.data.last_name}
                                    onChange={(e) =>
                                        form.setData(
                                            'last_name',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Okafor"
                                />
                            </Field>
                        </div>

                        <Field label="Date of birth" error={form.errors.dob}>
                            <input
                                type="date"
                                className={inputClass}
                                value={form.data.dob ?? ''}
                                onChange={(e) =>
                                    form.setData('dob', e.target.value)
                                }
                            />
                        </Field>

                        {/* A brand-new student can be dropped straight into a room. */}
                        {!editing && (
                            <Field label="Enrol in a class (optional)">
                                <select
                                    className={inputClass}
                                    value={form.data.classroom_id}
                                    onChange={(e) =>
                                        form.setData(
                                            'classroom_id',
                                            e.target.value,
                                        )
                                    }
                                >
                                    <option value="">— None for now —</option>
                                    {classes.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.name}
                                            {c.year ? ` · ${c.year}` : ''}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                        )}

                        <Field label="Notes" error={form.errors.notes}>
                            <textarea
                                className={cn(inputClass, 'min-h-20')}
                                value={form.data.notes ?? ''}
                                onChange={(e) =>
                                    form.setData('notes', e.target.value)
                                }
                                placeholder="Allergies, routines, anything staff should know."
                            />
                        </Field>

                        <button
                            type="submit"
                            disabled={
                                form.processing ||
                                form.data.first_name.trim() === '' ||
                                form.data.last_name.trim() === ''
                            }
                            className="inline-flex w-full items-center justify-center gap-2 rounded-[4px] bg-portal-accent py-3 text-[15px] font-bold text-white transition hover:brightness-95 disabled:bg-portal-field disabled:text-neutral-400"
                        >
                            {form.processing && (
                                <Loader2 className="size-4 animate-spin" />
                            )}
                            {editing ? 'Save changes' : 'Add student'}
                        </button>
                    </form>
                </DialogPanel>
            </div>
        </Dialog>
    );
}

/** Move a student into a class (creates a new enrolment, closes the old one). */
function AssignDialog({
    student,
    classes,
    onClose,
}: {
    student: DirectoryStudent | null;
    classes: DirectoryClass[];
    onClose: () => void;
}) {
    const form = useForm({
        classroom_id: student?.currentClass
            ? String(student.currentClass.id)
            : '',
    });

    if (!student) {
        return null;
    }

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post(`/portal/students/${student.id}/enroll`, {
            onSuccess: onClose,
        });
    };

    return (
        <Dialog open onClose={onClose} className="relative z-50">
            <DialogBackdrop
                transition
                className="fixed inset-0 bg-black/40 duration-150 data-closed:opacity-0"
            />
            <div className="fixed inset-0 overflow-y-auto p-4">
                <DialogPanel
                    transition
                    className="mx-auto my-auto w-full max-w-[460px] overflow-hidden rounded-[4px] bg-white shadow-s3 duration-150 data-closed:scale-95 data-closed:opacity-0"
                >
                    <div className="flex items-center justify-between border-b border-portal-line px-5 py-4">
                        <DialogTitle className="text-lg font-bold text-portal-ink">
                            Assign {student.firstName} to a class
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

                    <form onSubmit={submit} className="space-y-4 p-5">
                        {student.currentClass && (
                            <p className="rounded-[4px] bg-portal-field px-3 py-2 text-sm text-neutral-600">
                                Currently in{' '}
                                <span className="font-bold text-portal-ink">
                                    {student.currentClass.name}
                                </span>
                                . Moving them keeps the old class in their
                                history.
                            </p>
                        )}

                        <Field label="Class" error={form.errors.classroom_id}>
                            <select
                                className={inputClass}
                                value={form.data.classroom_id}
                                onChange={(e) =>
                                    form.setData('classroom_id', e.target.value)
                                }
                            >
                                <option value="">— Choose a class —</option>
                                {classes.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.name}
                                        {c.year ? ` · ${c.year}` : ''}
                                    </option>
                                ))}
                            </select>
                        </Field>

                        <button
                            type="submit"
                            disabled={
                                form.processing || form.data.classroom_id === ''
                            }
                            className="inline-flex w-full items-center justify-center gap-2 rounded-[4px] bg-portal-accent py-3 text-[15px] font-bold text-white transition hover:brightness-95 disabled:bg-portal-field disabled:text-neutral-400"
                        >
                            {form.processing && (
                                <Loader2 className="size-4 animate-spin" />
                            )}
                            Save
                        </button>
                    </form>
                </DialogPanel>
            </div>
        </Dialog>
    );
}

function StudentRow({
    student,
    onOpen,
    onAssign,
    onEdit,
}: {
    student: DirectoryStudent;
    onOpen: (s: DirectoryStudent) => void;
    onAssign: (s: DirectoryStudent) => void;
    onEdit: (s: DirectoryStudent) => void;
}) {
    const [open, setOpen] = useState(false);
    const past = student.history.filter((h) => !h.current);

    return (
        <div className="border-b border-portal-line last:border-0">
            <div className="flex items-center gap-3 px-4 py-3">
                <button
                    type="button"
                    onClick={() => onOpen(student)}
                    className="flex min-w-0 flex-1 items-center gap-3 rounded-[4px] text-left transition hover:opacity-80"
                >
                    <Avatar name={student.name} src={student.photo} />

                    <span className="min-w-0 flex-1">
                        <span className="block truncate text-[15px] font-bold text-portal-ink">
                            {student.name}
                        </span>
                        <span className="block truncate text-sm text-neutral-500">
                            {student.age !== null
                                ? `${student.age === 0 ? '<1' : student.age} yr`
                                : 'Age not set'}
                            {student.guardianCount > 0 &&
                                ` · ${student.guardianCount} ${student.guardianCount === 1 ? 'family' : 'families'}`}
                        </span>
                    </span>
                </button>

                {student.currentClass ? (
                    <span className="hidden shrink-0 rounded-full bg-portal-soft px-3 py-1 text-xs font-bold text-portal-accent sm:inline">
                        {student.currentClass.name}
                    </span>
                ) : (
                    <span className="hidden shrink-0 rounded-full bg-neutral-100 px-3 py-1 text-xs font-bold text-neutral-400 sm:inline">
                        Unassigned
                    </span>
                )}

                {past.length > 0 && (
                    <button
                        type="button"
                        onClick={() => setOpen((v) => !v)}
                        className="hidden shrink-0 items-center gap-1 rounded-[4px] px-2 py-1 text-xs font-medium text-neutral-400 transition hover:bg-portal-field hover:text-portal-ink sm:flex"
                    >
                        <Clock className="size-3.5" />
                        {past.length} past
                        <ChevronDown
                            className={cn(
                                'size-3.5 transition-transform',
                                open && 'rotate-180',
                            )}
                        />
                    </button>
                )}

                <Menu as="div" className="relative shrink-0">
                    <MenuButton
                        aria-label={`Manage ${student.name}`}
                        className="grid size-9 place-items-center rounded-[4px] text-neutral-400 transition hover:bg-portal-field hover:text-portal-ink"
                    >
                        <MoreVertical className="size-4.5" />
                    </MenuButton>
                    <MenuItems
                        anchor="bottom end"
                        className="z-50 mt-1 w-48 rounded-[4px] border border-portal-line bg-white py-1 text-sm shadow-s3 focus:outline-none"
                    >
                        <MenuItem>
                            <button
                                type="button"
                                onClick={() => onAssign(student)}
                                className="flex w-full items-center gap-2 px-3 py-2 text-left font-medium text-portal-ink data-focus:bg-portal-field"
                            >
                                <ArrowRightLeft className="size-4 text-neutral-400" />
                                {student.currentClass
                                    ? 'Move to class'
                                    : 'Assign to class'}
                            </button>
                        </MenuItem>
                        <MenuItem>
                            <button
                                type="button"
                                onClick={() => onEdit(student)}
                                className="flex w-full items-center gap-2 px-3 py-2 text-left font-medium text-portal-ink data-focus:bg-portal-field"
                            >
                                <Pencil className="size-4 text-neutral-400" />
                                Edit details
                            </button>
                        </MenuItem>
                    </MenuItems>
                </Menu>
            </div>

            {open && past.length > 0 && (
                <ol className="space-y-1 border-t border-portal-line bg-portal-field/40 px-4 py-3 pl-16 text-sm">
                    {past.map((h) => (
                        <li
                            key={h.id}
                            className="flex items-center justify-between gap-3 text-neutral-500"
                        >
                            <span className="font-medium text-portal-ink">
                                {h.classroom ?? 'Unknown class'}
                                {h.year ? ` · ${h.year}` : ''}
                            </span>
                            <span className="shrink-0 text-xs">
                                {h.startedOn ?? '?'} – {h.endedOn ?? '?'}
                            </span>
                        </li>
                    ))}
                </ol>
            )}
        </div>
    );
}

function DetailSection({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    return (
        <div>
            <h3 className="mb-2 text-xs font-bold tracking-wide text-neutral-400 uppercase">
                {title}
            </h3>
            {children}
        </div>
    );
}

/** The read-only student card: code, class history, family, report cards. */
function StudentDetail({
    student,
    onClose,
    onAssign,
    onEdit,
}: {
    student: DirectoryStudent | null;
    onClose: () => void;
    onAssign: (s: DirectoryStudent) => void;
    onEdit: (s: DirectoryStudent) => void;
}) {
    const [copied, setCopied] = useState(false);

    if (!student) {
        return null;
    }

    const copyCode = () => {
        if (!student.inviteCode) {
            return;
        }

        navigator.clipboard?.writeText(student.inviteCode).then(() => {
            setCopied(true);
            window.setTimeout(() => setCopied(false), 1500);
        });
    };

    return (
        <Dialog open onClose={onClose} className="relative z-50">
            <DialogBackdrop
                transition
                className="fixed inset-0 bg-black/40 duration-150 data-closed:opacity-0"
            />
            <div className="fixed inset-0 overflow-y-auto p-4">
                <DialogPanel
                    transition
                    className="mx-auto my-auto w-full max-w-[560px] overflow-hidden rounded-[4px] bg-white shadow-s3 duration-150 data-closed:scale-95 data-closed:opacity-0"
                >
                    <div className="flex items-start gap-3 border-b border-portal-line p-5">
                        <Avatar name={student.name} src={student.photo} />
                        <div className="min-w-0 flex-1">
                            <DialogTitle className="truncate text-lg font-bold text-portal-ink">
                                {student.name}
                            </DialogTitle>
                            <p className="text-sm text-neutral-500">
                                {student.age !== null
                                    ? `${student.age === 0 ? '<1' : student.age} yr`
                                    : 'Age not set'}
                                {student.dob ? ` · born ${student.dob}` : ''}
                            </p>
                        </div>
                        {student.currentClass ? (
                            <span className="shrink-0 rounded-full bg-portal-soft px-3 py-1 text-xs font-bold text-portal-accent">
                                {student.currentClass.name}
                            </span>
                        ) : (
                            <span className="shrink-0 rounded-full bg-neutral-100 px-3 py-1 text-xs font-bold text-neutral-400">
                                Unassigned
                            </span>
                        )}
                        <button
                            type="button"
                            onClick={onClose}
                            aria-label="Close"
                            className="grid size-9 shrink-0 place-items-center rounded-[4px] bg-portal-field text-portal-ink transition hover:bg-neutral-200"
                        >
                            <X className="size-4.5" />
                        </button>
                    </div>

                    <div className="max-h-[60vh] space-y-5 overflow-y-auto p-5">
                        <DetailSection title="Student code">
                            <div className="flex items-center gap-2">
                                <code className="rounded-[4px] bg-portal-field px-3 py-2 font-mono text-[15px] font-bold tracking-wider text-portal-ink">
                                    {student.inviteCode ?? '—'}
                                </code>
                                {student.inviteCode && (
                                    <button
                                        type="button"
                                        onClick={copyCode}
                                        className="inline-flex items-center gap-1.5 rounded-[4px] border border-portal-line px-3 py-2 text-sm font-medium text-neutral-600 transition hover:bg-portal-field"
                                    >
                                        {copied ? (
                                            <Check className="size-4 text-green-600" />
                                        ) : (
                                            <Copy className="size-4" />
                                        )}
                                        {copied ? 'Copied' : 'Copy'}
                                    </button>
                                )}
                            </div>
                            <p className="mt-1.5 text-xs text-neutral-400">
                                Families redeem this code to link to{' '}
                                {student.firstName}.
                            </p>
                        </DetailSection>

                        <DetailSection title="Class history">
                            {student.history.length === 0 ? (
                                <p className="text-sm text-neutral-400">
                                    No class history yet.
                                </p>
                            ) : (
                                <ol className="space-y-2">
                                    {student.history.map((h) => (
                                        <li
                                            key={h.id}
                                            className="flex items-center gap-3"
                                        >
                                            <span
                                                className={cn(
                                                    'size-2 shrink-0 rounded-full',
                                                    h.current
                                                        ? 'bg-portal-accent'
                                                        : 'bg-neutral-300',
                                                )}
                                            />
                                            <span className="min-w-0 flex-1 truncate">
                                                <span className="text-[15px] font-bold text-portal-ink">
                                                    {h.classroom ?? 'Unknown'}
                                                </span>
                                                {h.year && (
                                                    <span className="text-sm text-neutral-400">
                                                        {' '}
                                                        · {h.year}
                                                    </span>
                                                )}
                                            </span>
                                            <span className="shrink-0 text-xs text-neutral-400">
                                                {h.current
                                                    ? 'Current'
                                                    : `${h.startedOn ?? '?'} – ${h.endedOn ?? '?'}`}
                                            </span>
                                        </li>
                                    ))}
                                </ol>
                            )}
                        </DetailSection>

                        <DetailSection title="Family">
                            {student.guardians.length === 0 ? (
                                <p className="text-sm text-neutral-400">
                                    No family linked yet — share the student
                                    code above.
                                </p>
                            ) : (
                                <ul className="space-y-2">
                                    {student.guardians.map((g) => (
                                        <li
                                            key={g.id}
                                            className="flex items-center gap-3 rounded-[4px] border border-portal-line px-3 py-2"
                                        >
                                            <span className="grid size-9 shrink-0 place-items-center rounded-full bg-portal-soft text-sm font-bold text-portal-accent">
                                                {g.name.charAt(0)}
                                            </span>
                                            <span className="min-w-0 flex-1">
                                                <span className="block truncate text-sm font-bold text-portal-ink">
                                                    {g.name}
                                                    {g.relationship && (
                                                        <span className="font-normal text-neutral-400">
                                                            {' '}
                                                            · {g.relationship}
                                                        </span>
                                                    )}
                                                </span>
                                                <span className="block truncate text-xs text-neutral-500">
                                                    {g.email}
                                                </span>
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </DetailSection>

                        <DetailSection title="Report cards">
                            {student.reportCards.length === 0 ? (
                                <p className="text-sm text-neutral-400">
                                    No report cards yet.
                                </p>
                            ) : (
                                <ul className="space-y-2">
                                    {student.reportCards.map((r) => (
                                        <li
                                            key={r.id}
                                            className="flex items-center gap-3 rounded-[4px] border border-portal-line px-3 py-2"
                                        >
                                            <FileText className="size-4 shrink-0 text-neutral-400" />
                                            <span className="min-w-0 flex-1">
                                                <span className="block truncate text-sm font-bold text-portal-ink">
                                                    {r.title}
                                                </span>
                                                <span className="block truncate text-xs text-neutral-500">
                                                    {r.issuedOn ?? 'No date'}
                                                    {!r.published && ' · Draft'}
                                                </span>
                                            </span>
                                            <a
                                                href={r.file.url}
                                                className="inline-flex shrink-0 items-center gap-1 rounded-[4px] px-2 py-1 text-xs font-medium text-portal-accent transition hover:bg-portal-field"
                                            >
                                                <Download className="size-3.5" />
                                                Download
                                            </a>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </DetailSection>

                        {student.notes && (
                            <DetailSection title="Notes">
                                <p className="text-sm whitespace-pre-line text-neutral-600">
                                    {student.notes}
                                </p>
                            </DetailSection>
                        )}
                    </div>

                    <div className="flex gap-2 border-t border-portal-line p-4">
                        <button
                            type="button"
                            onClick={() => {
                                onClose();
                                onAssign(student);
                            }}
                            className="inline-flex flex-1 items-center justify-center gap-1.5 rounded-[4px] border border-portal-line py-2.5 text-sm font-bold text-portal-ink transition hover:bg-portal-field"
                        >
                            <ArrowRightLeft className="size-4" />
                            {student.currentClass
                                ? 'Move class'
                                : 'Assign class'}
                        </button>
                        <button
                            type="button"
                            onClick={() => {
                                onClose();
                                onEdit(student);
                            }}
                            className="inline-flex flex-1 items-center justify-center gap-1.5 rounded-[4px] bg-portal-accent py-2.5 text-sm font-bold text-white transition hover:brightness-95"
                        >
                            <Pencil className="size-4" />
                            Edit
                        </button>
                    </div>
                </DialogPanel>
            </div>
        </Dialog>
    );
}

export default function StudentsDirectory({
    students,
    classes,
}: {
    students: DirectoryStudent[];
    classes: DirectoryClass[];
}) {
    const [query, setQuery] = useState('');
    // null = closed, 'new' = add, a student = edit that one.
    const [dialog, setDialog] = useState<DirectoryStudent | 'new' | null>(null);
    const [assigning, setAssigning] = useState<DirectoryStudent | null>(null);
    const [detail, setDetail] = useState<DirectoryStudent | null>(null);

    const filtered = useMemo(() => {
        const q = query.trim().toLowerCase();

        if (q === '') {
            return students;
        }

        return students.filter((s) => s.name.toLowerCase().includes(q));
    }, [students, query]);

    return (
        <>
            <Head title="Students" />
            <div className="py-6">
                <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-xl font-bold text-portal-ink">
                            Students
                        </h1>
                        <p className="text-sm text-neutral-500">
                            {students.length}{' '}
                            {students.length === 1 ? 'student' : 'students'} in
                            the directory
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={() => setDialog('new')}
                        className="inline-flex items-center gap-1.5 rounded-[4px] bg-portal-accent px-4 py-2 text-sm font-bold text-white transition hover:brightness-95"
                    >
                        <Plus className="size-4" />
                        Add student
                    </button>
                </div>

                <div className="relative mb-4">
                    <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-neutral-400" />
                    <input
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder="Search students"
                        className="w-full rounded-[4px] border border-portal-line py-2.5 pr-3 pl-9 text-[15px] outline-none focus:border-portal-accent"
                    />
                </div>

                <div className="overflow-hidden rounded-[4px] border border-portal-line bg-white">
                    {filtered.length === 0 ? (
                        <div className="grid place-items-center px-4 py-16 text-center">
                            <Users className="size-8 text-neutral-300" />
                            <p className="mt-3 text-[15px] font-bold text-portal-ink">
                                {students.length === 0
                                    ? 'No students yet'
                                    : 'No matches'}
                            </p>
                            <p className="mt-1 max-w-sm text-sm text-neutral-500">
                                {students.length === 0
                                    ? 'Add your first student, then assign them to a class.'
                                    : 'Try a different name.'}
                            </p>
                        </div>
                    ) : (
                        filtered.map((student) => (
                            <StudentRow
                                key={student.id}
                                student={student}
                                onOpen={setDetail}
                                onAssign={setAssigning}
                                onEdit={setDialog}
                            />
                        ))
                    )}
                </div>
            </div>

            <StudentDialog
                key={dialog === 'new' ? 'new' : (dialog?.id ?? 'none')}
                editing={dialog === 'new' ? null : dialog}
                classes={classes}
                open={dialog !== null}
                onClose={() => setDialog(null)}
            />

            <AssignDialog
                key={assigning?.id ?? 'none'}
                student={assigning}
                classes={classes}
                onClose={() => setAssigning(null)}
            />

            <StudentDetail
                student={detail}
                onClose={() => setDetail(null)}
                onAssign={setAssigning}
                onEdit={setDialog}
            />
        </>
    );
}
