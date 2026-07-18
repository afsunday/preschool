import { Head, router, useForm } from '@inertiajs/react';
import { GraduationCap, Pencil, Plus, ShieldCheck, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { ConfirmDialog } from '@/components/confirm-dialog';
import SideModal from '@/components/side-modal';

type Member = {
    id: number;
    name: string;
    firstName: string;
    lastName: string;
    email: string;
    hasAdminAccess: boolean;
    isSuper: boolean;
    permissions: string[];
    classCount: number;
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
    has_admin_access: false,
    is_super: false,
    permissions: [] as string[],
};

export default function TeamIndex({
    members,
    groups,
}: {
    members: Member[];
    groups: Group[];
}) {
    const [open, setOpen] = useState(false);
    const [editing, setEditing] = useState<Member | null>(null);
    const [pending, setPending] = useState<Member | null>(null);

    const form = useForm<typeof empty>(empty);

    const openCreate = () => {
        setEditing(null);
        form.setData(empty);
        form.clearErrors();
        setOpen(true);
    };

    const openEdit = (m: Member) => {
        setEditing(m);
        form.setData({
            first_name: m.firstName,
            last_name: m.lastName,
            email: m.email,
            password: '',
            has_admin_access: m.hasAdminAccess,
            is_super: m.isSuper,
            permissions: m.permissions,
        });
        form.clearErrors();
        setOpen(true);
    };

    const togglePermission = (name: string) => {
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
                toast.success(editing ? 'Team member updated' : 'Team member added');
            },
        };

        if (editing) {
            form.put(`/admin/team/${editing.id}`, opts);
        } else {
            form.post('/admin/team', opts);
        }
    };

    const destroy = () => {
        if (!pending) {
            return;
        }

        router.delete(`/admin/team/${pending.id}`, {
            preserveScroll: true,
            onSuccess: () => toast.success('Team member removed'),
        });
        setPending(null);
    };

    return (
        <>
            <Head title="Team" />

            <div className="flex h-full flex-col gap-6 p-4">
                <div className="flex items-end justify-between gap-4">
                    <div>
                        <h1 className="text-xl font-semibold tracking-tight">
                            Team
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Teachers who run your rooms and back-office access, in
                            one place.
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={openCreate}
                        className="btn-black inline-flex items-center gap-1.5 whitespace-nowrap"
                    >
                        <Plus className="size-4" /> New team member
                    </button>
                </div>

                <div className="overflow-hidden rounded-[4px] border border-black/10">
                    <table className="w-full text-sm">
                        <thead className="bg-neutral-50 text-left text-xs text-neutral-500 uppercase">
                            <tr>
                                <th className="px-4 py-2 font-medium">Name</th>
                                <th className="px-4 py-2 font-medium">Email</th>
                                <th className="px-4 py-2 font-medium">Role</th>
                                <th className="px-4 py-2 font-medium">Classes</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-black/5">
                            {members.map((m) => (
                                <tr key={m.id} className="hover:bg-neutral-50">
                                    <td className="px-4 py-2.5 font-medium">
                                        {m.name}
                                        {m.isSelf && (
                                            <span className="ml-1.5 text-xs text-neutral-400">
                                                (you)
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-4 py-2.5 text-neutral-500">
                                        {m.email}
                                    </td>
                                    <td className="px-4 py-2.5">
                                        <RoleBadge member={m} />
                                    </td>
                                    <td className="px-4 py-2.5 text-neutral-500">
                                        {m.classCount}
                                    </td>
                                    <td className="px-4 py-2.5">
                                        <div className="flex items-center justify-end gap-1">
                                            <button
                                                type="button"
                                                onClick={() => openEdit(m)}
                                                className="inline-flex items-center gap-1 rounded-[4px] px-2 py-1 text-neutral-600 hover:bg-neutral-100"
                                            >
                                                <Pencil className="size-4" /> Edit
                                            </button>
                                            {!m.isSelf && (
                                                <button
                                                    type="button"
                                                    onClick={() => setPending(m)}
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
                    {editing ? 'Edit team member' : 'New team member'}
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
                                        form.setData('first_name', e.target.value)
                                    }
                                />
                            </Field>
                            <Field label="Last name">
                                <input
                                    className="form-control"
                                    value={form.data.last_name}
                                    onChange={(e) =>
                                        form.setData('last_name', e.target.value)
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

                        <div className="rounded-[4px] border border-black/10 p-3">
                            <label className="flex cursor-pointer items-start gap-2.5">
                                <input
                                    type="checkbox"
                                    className="checkbox mt-0.5"
                                    checked={form.data.has_admin_access}
                                    onChange={(e) =>
                                        form.setData(
                                            'has_admin_access',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>
                                    <span className="block text-sm font-medium">
                                        Back-office access
                                    </span>
                                    <span className="block text-xs text-neutral-500">
                                        Let this person into the admin. Teachers
                                        without it only run their rooms.
                                    </span>
                                </span>
                            </label>

                            {form.data.has_admin_access && (
                                <div className="mt-3 space-y-3 border-t border-black/5 pt-3">
                                    <label className="flex cursor-pointer items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            className="checkbox"
                                            checked={form.data.is_super}
                                            onChange={(e) =>
                                                form.setData(
                                                    'is_super',
                                                    e.target.checked,
                                                )
                                            }
                                        />
                                        <span className="font-medium">
                                            Full access
                                        </span>
                                        <span className="text-xs text-neutral-500">
                                            every area, no need to tick boxes
                                        </span>
                                    </label>

                                    {!form.data.is_super && (
                                        <div className="space-y-3">
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
                                                                    className="checkbox"
                                                                    checked={form.data.permissions.includes(
                                                                        p.name,
                                                                    )}
                                                                    onChange={() =>
                                                                        togglePermission(
                                                                            p.name,
                                                                        )
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
                                {editing ? 'Save' : 'Add member'}
                            </button>
                        </div>
                    </form>
                </div>
            </SideModal>

            <ConfirmDialog
                open={pending !== null}
                title="Remove team member?"
                message={
                    pending
                        ? `${pending.name}'s classes will be left unassigned.`
                        : undefined
                }
                onConfirm={destroy}
                onClose={() => setPending(null)}
            />
        </>
    );
}

function RoleBadge({ member }: { member: Member }) {
    if (member.hasAdminAccess) {
        return (
            <span className="bg-wodi-pink/10 text-wodi-pink inline-flex items-center gap-1 rounded-[4px] px-2 py-0.5 text-xs font-medium">
                <ShieldCheck className="size-3.5" />
                {member.isSuper
                    ? 'Admin · full access'
                    : `Admin · ${member.permissions.length} permission${member.permissions.length === 1 ? '' : 's'}`}
            </span>
        );
    }

    return (
        <span className="inline-flex items-center gap-1 rounded-[4px] bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600">
            <GraduationCap className="size-3.5" />
            Teacher
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
    children: React.ReactNode;
}) {
    return (
        <label className="block">
            <span className="text-xs font-medium text-neutral-600">{label}</span>
            <div className="mt-1.5">{children}</div>
            {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
        </label>
    );
}

TeamIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Team', href: '/admin/team' },
    ],
};
