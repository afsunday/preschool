import { Head, router, useForm } from '@inertiajs/react';
import { Pencil, Plus, ShieldCheck, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { ConfirmDialog } from '@/components/confirm-dialog';
import SideModal from '@/components/side-modal';

type Staff = {
    id: number;
    name: string;
    firstName: string;
    lastName: string;
    email: string;
    isSuper: boolean;
    permissions: string[];
    isSelf: boolean;
};

type Group = {
    name: string;
    permissions: { name: string; label: string }[];
};

const empty = {
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    permissions: [] as string[],
};

export default function StaffIndex({
    staff,
    groups,
}: {
    staff: Staff[];
    groups: Group[];
}) {
    const [open, setOpen] = useState(false);
    const [editing, setEditing] = useState<Staff | null>(null);
    const [pending, setPending] = useState<Staff | null>(null);

    const form = useForm<typeof empty>(empty);

    const openCreate = () => {
        setEditing(null);
        form.setData(empty);
        form.clearErrors();
        setOpen(true);
    };

    const openEdit = (s: Staff) => {
        setEditing(s);
        form.setData({
            first_name: s.firstName,
            last_name: s.lastName,
            email: s.email,
            password: '',
            permissions: s.permissions,
        });
        form.clearErrors();
        setOpen(true);
    };

    const toggle = (name: string) => {
        const has = form.data.permissions.includes(name);
        form.setData(
            'permissions',
            has
                ? form.data.permissions.filter((p) => p !== name)
                : [...form.data.permissions, name],
        );
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        const opts = {
            preserveScroll: true,
            onSuccess: () => {
                setOpen(false);
                toast.success(editing ? 'Staff updated' : 'Staff added');
            },
        };

        if (editing) {
            form.put(`/admin/staff/${editing.id}`, opts);
        } else {
            form.post('/admin/staff', opts);
        }
    };

    const destroy = () => {
        if (!pending) {
            return;
        }

        router.delete(`/admin/staff/${pending.id}`, {
            preserveScroll: true,
            onSuccess: () => toast.success('Staff removed'),
        });
        setPending(null);
    };

    return (
        <>
            <Head title="Staff" />

            <div className="flex h-full flex-col gap-6 p-4">
                <div className="flex items-end justify-between gap-4">
                    <div>
                        <h1 className="text-xl font-semibold tracking-tight">
                            Staff
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Back-office users and what each one can access.
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={openCreate}
                        className="btn-black inline-flex items-center gap-1.5 whitespace-nowrap"
                    >
                        <Plus className="size-4" /> New staff member
                    </button>
                </div>

                <div className="overflow-hidden rounded-[4px] border border-black/10">
                    <table className="w-full text-sm">
                        <thead className="bg-neutral-50 text-left text-xs text-neutral-500 uppercase">
                            <tr>
                                <th className="px-4 py-2 font-medium">Name</th>
                                <th className="px-4 py-2 font-medium">Email</th>
                                <th className="px-4 py-2 font-medium">
                                    Access
                                </th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-black/5">
                            {staff.map((s) => (
                                <tr key={s.id} className="hover:bg-neutral-50">
                                    <td className="px-4 py-2.5 font-medium">
                                        {s.name}
                                        {s.isSelf && (
                                            <span className="ml-1.5 text-xs text-neutral-400">
                                                (you)
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-4 py-2.5 text-neutral-500">
                                        {s.email}
                                    </td>
                                    <td className="px-4 py-2.5">
                                        {s.isSuper ? (
                                            <span className="bg-wodi-pink/10 text-wodi-pink inline-flex items-center gap-1 rounded-[4px] px-2 py-0.5 text-xs font-medium">
                                                <ShieldCheck className="size-3.5" />
                                                Full access
                                            </span>
                                        ) : (
                                            <span className="text-neutral-500">
                                                {s.permissions.length}{' '}
                                                permission
                                                {s.permissions.length === 1
                                                    ? ''
                                                    : 's'}
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-4 py-2.5">
                                        <div className="flex items-center justify-end gap-1">
                                            <button
                                                type="button"
                                                onClick={() => openEdit(s)}
                                                className="inline-flex items-center gap-1 rounded-[4px] px-2 py-1 text-neutral-600 hover:bg-neutral-100"
                                            >
                                                <Pencil className="size-4" />{' '}
                                                Edit
                                            </button>
                                            {!s.isSelf && (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setPending(s)
                                                    }
                                                    className="rounded-[4px] p-1 text-neutral-500 hover:bg-red-50 hover:text-red-500"
                                                >
                                                    <Trash2 className="size-4" />
                                                </button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            <SideModal
                hidden={open}
                onClose={() => setOpen(false)}
                sizeClassName="xs:max-w-[480px]"
            >
                <span title="title" className="font-semibold">
                    {editing ? 'Edit staff member' : 'New staff member'}
                </span>

                <div title="body" data-slot="body" className="p-4">
                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid grid-cols-2 gap-3">
                            <Field
                                label="First name"
                                error={form.errors.first_name}
                            >
                                <input
                                    className="form-control"
                                    value={form.data.first_name}
                                    onChange={(e) =>
                                        form.setData(
                                            'first_name',
                                            e.target.value,
                                        )
                                    }
                                />
                            </Field>
                            <Field label="Last name">
                                <input
                                    className="form-control"
                                    value={form.data.last_name}
                                    onChange={(e) =>
                                        form.setData(
                                            'last_name',
                                            e.target.value,
                                        )
                                    }
                                />
                            </Field>
                        </div>

                        <Field label="Email" error={form.errors.email}>
                            <input
                                type="email"
                                className="form-control"
                                value={form.data.email}
                                onChange={(e) =>
                                    form.setData('email', e.target.value)
                                }
                            />
                        </Field>

                        <Field
                            label={
                                editing
                                    ? 'Password (leave blank to keep)'
                                    : 'Password'
                            }
                            error={form.errors.password}
                        >
                            <input
                                type="password"
                                className="form-control"
                                autoComplete="new-password"
                                value={form.data.password}
                                onChange={(e) =>
                                    form.setData('password', e.target.value)
                                }
                            />
                        </Field>

                        <div>
                            <span className="text-xs font-medium text-neutral-600">
                                Permissions
                            </span>

                            {editing?.isSuper ? (
                                <p className="bg-wodi-pink/5 text-wodi-pink mt-2 rounded-[4px] px-3 py-2 text-xs">
                                    This user is a super admin with full access.
                                </p>
                            ) : (
                                <div className="mt-2 space-y-3">
                                    {groups.map((g) => (
                                        <div key={g.name}>
                                            <div className="text-[11px] font-semibold text-neutral-400 uppercase">
                                                {g.name}
                                            </div>
                                            <div className="mt-1 space-y-1">
                                                {g.permissions.map((p) => (
                                                    <label
                                                        key={p.name}
                                                        className="flex cursor-pointer items-center gap-2 text-sm"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            className="size-4 rounded border-black/20"
                                                            checked={form.data.permissions.includes(
                                                                p.name,
                                                            )}
                                                            onChange={() =>
                                                                toggle(p.name)
                                                            }
                                                        />
                                                        {p.label}
                                                    </label>
                                                ))}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>

                        <div className="flex justify-end gap-2 pt-2">
                            <button
                                type="button"
                                onClick={() => setOpen(false)}
                                className="btn-light"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={form.processing}
                                className="btn-black"
                            >
                                {editing ? 'Save' : 'Add staff'}
                            </button>
                        </div>
                    </form>
                </div>
            </SideModal>

            <ConfirmDialog
                open={pending !== null}
                title="Remove staff member?"
                message={pending?.name}
                onConfirm={destroy}
                onClose={() => setPending(null)}
            />
        </>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <label className="block">
            <span className="text-xs font-medium text-neutral-600">
                {label}
            </span>
            <div className="mt-1.5">{children}</div>
            {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
        </label>
    );
}

StaffIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Staff', href: '/admin/staff' },
    ],
};
