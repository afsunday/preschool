import {
    Dialog,
    DialogBackdrop,
    DialogPanel,
    DialogTitle,
} from '@headlessui/react';
import { Head, router, useForm } from '@inertiajs/react';
import { Pencil, Plus, Star, Tag, Trash2, X } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { createHttpMediaApi, MediaPicker } from '@/cms/media';
import type { MediaItem } from '@/cms/media';
import { ConfirmDialog } from '@/components/confirm-dialog';
import SideModal from '@/components/side-modal';

type Pending = { title: string; message?: string; run: () => void };

type Material = {
    id: number;
    title: string;
    description: string | null;
    categoryId: number | null;
    category: string | null;
    type: string;
    url: string | null;
    imagePath: string | null;
    isFeatured: boolean;
    isPublished: boolean;
};

type Category = {
    id: number;
    name: string;
    slug: string;
    materialsCount: number;
};

const mediaApi = createHttpMediaApi('/admin/media/items');

const empty = {
    title: '',
    description: '',
    category_id: '' as number | '',
    type: 'article',
    url: '',
    image_path: '',
    is_featured: false,
    is_published: true,
};

// A path is all we store; wrap it so the picker can show a thumbnail.
function pathAsItem(path: string): MediaItem | null {
    if (!path) {
        return null;
    }

    return {
        id: 0,
        url: path,
        kind: 'image',
        mimeType: null,
        originalName: path,
        title: null,
        alt: null,
        description: null,
        size: 0,
        width: null,
        height: null,
        createdAt: null,
    };
}

export default function MaterialsIndex({
    materials,
    categories,
    types,
}: {
    materials: Material[];
    categories: Category[];
    types: string[];
}) {
    const [open, setOpen] = useState(false);
    const [editing, setEditing] = useState<Material | null>(null);
    const [manageCats, setManageCats] = useState(false);
    const [pending, setPending] = useState<Pending | null>(null);

    const form = useForm<typeof empty>(empty);

    const openCreate = () => {
        setEditing(null);
        form.setData(empty);
        form.clearErrors();
        setOpen(true);
    };

    const openEdit = (m: Material) => {
        setEditing(m);
        form.setData({
            title: m.title,
            description: m.description ?? '',
            category_id: m.categoryId ?? '',
            type: m.type,
            url: m.url ?? '',
            image_path: m.imagePath ?? '',
            is_featured: m.isFeatured,
            is_published: m.isPublished,
        });
        form.clearErrors();
        setOpen(true);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        const opts = {
            preserveScroll: true,
            onSuccess: () => {
                setOpen(false);
                toast.success(
                    editing ? 'Material updated' : 'Material created',
                );
            },
        };

        // '' → null so the nullable/integer rule is happy.
        form.transform((data) => ({
            ...data,
            category_id: data.category_id === '' ? null : data.category_id,
        }));

        if (editing) {
            form.put(`/admin/materials/${editing.id}`, opts);
        } else {
            form.post('/admin/materials', opts);
        }
    };

    const remove = (m: Material) => {
        setPending({
            title: `Delete “${m.title}”?`,
            message: 'This cannot be undone.',
            run: () =>
                router.delete(`/admin/materials/${m.id}`, {
                    preserveScroll: true,
                    onSuccess: () => toast.success('Material deleted'),
                }),
        });
    };

    return (
        <>
            <Head title="Resources" />

            <div className="flex h-full flex-col gap-6 p-4">
                <div className="flex items-end justify-between gap-4">
                    <div>
                        <h1 className="text-xl font-semibold tracking-tight">
                            Resources
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            The learning materials shown on the home teaser and
                            the resources page.
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={() => setManageCats((v) => !v)}
                            className="btn-light inline-flex items-center gap-1.5 whitespace-nowrap"
                        >
                            <Tag className="size-4" /> Categories
                        </button>
                        <button
                            type="button"
                            onClick={openCreate}
                            className="btn-black inline-flex items-center gap-1.5 whitespace-nowrap"
                        >
                            <Plus className="size-4" /> New material
                        </button>
                    </div>
                </div>

                <div className="overflow-hidden rounded-[4px] border border-black/10">
                    <table className="w-full text-sm">
                        <thead className="bg-neutral-50 text-left text-xs text-neutral-500 uppercase">
                            <tr>
                                <th className="px-4 py-2 font-medium">Title</th>
                                <th className="px-4 py-2 font-medium">
                                    Category
                                </th>
                                <th className="px-4 py-2 font-medium">Type</th>
                                <th className="px-4 py-2 font-medium">
                                    Status
                                </th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-black/5">
                            {materials.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="px-4 py-8 text-center text-neutral-400"
                                    >
                                        No materials yet. Add one above.
                                    </td>
                                </tr>
                            )}
                            {materials.map((m) => (
                                <tr key={m.id} className="hover:bg-neutral-50">
                                    <td className="px-4 py-2.5">
                                        <div className="flex items-center gap-2 font-medium">
                                            {m.isFeatured && (
                                                <Star className="size-3.5 shrink-0 fill-amber-400 text-amber-400" />
                                            )}
                                            <span className="line-clamp-1">
                                                {m.title}
                                            </span>
                                        </div>
                                    </td>
                                    <td className="px-4 py-2.5 text-neutral-500">
                                        {m.category ?? '—'}
                                    </td>
                                    <td className="px-4 py-2.5 text-neutral-500 capitalize">
                                        {m.type}
                                    </td>
                                    <td className="px-4 py-2.5">
                                        <span
                                            className={
                                                m.isPublished
                                                    ? 'rounded-[4px] bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700'
                                                    : 'rounded-[4px] bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600'
                                            }
                                        >
                                            {m.isPublished
                                                ? 'published'
                                                : 'draft'}
                                        </span>
                                    </td>
                                    <td className="px-4 py-2.5">
                                        <div className="flex items-center justify-end gap-1">
                                            <button
                                                type="button"
                                                onClick={() => openEdit(m)}
                                                className="inline-flex items-center gap-1 rounded-[4px] px-2 py-1 text-neutral-600 hover:bg-neutral-100"
                                            >
                                                <Pencil className="size-4" />{' '}
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => remove(m)}
                                                className="rounded-[4px] p-1 text-neutral-500 hover:bg-red-50 hover:text-red-500"
                                            >
                                                <Trash2 className="size-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            <Dialog
                open={open}
                onClose={() => setOpen(false)}
                className="relative z-50"
            >
                <DialogBackdrop className="fixed inset-0 bg-black/50" />
                <div className="fixed inset-0 flex items-center justify-center p-4">
                    <DialogPanel className="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-[6px] bg-white shadow-xl">
                        <div className="flex items-center justify-between border-b border-black/10 px-5 py-3">
                            <DialogTitle className="text-sm font-semibold">
                                {editing ? 'Edit material' : 'New material'}
                            </DialogTitle>
                            <button
                                type="button"
                                onClick={() => setOpen(false)}
                                className="rounded p-1 text-neutral-400 hover:text-neutral-700"
                            >
                                <X className="size-4" />
                            </button>
                        </div>

                        <form
                            onSubmit={submit}
                            className="flex-1 space-y-4 overflow-y-auto p-5"
                        >
                            <Field label="Title" error={form.errors.title}>
                                <input
                                    className="form-control"
                                    value={form.data.title}
                                    onChange={(e) =>
                                        form.setData('title', e.target.value)
                                    }
                                    autoFocus
                                />
                            </Field>

                            <Field
                                label="Description"
                                error={form.errors.description}
                            >
                                <textarea
                                    className="form-control"
                                    rows={2}
                                    value={form.data.description}
                                    onChange={(e) =>
                                        form.setData(
                                            'description',
                                            e.target.value,
                                        )
                                    }
                                />
                            </Field>

                            <div className="grid grid-cols-2 gap-4">
                                <Field
                                    label="Category"
                                    error={form.errors.category_id}
                                >
                                    <select
                                        className="form-control"
                                        value={form.data.category_id}
                                        onChange={(e) =>
                                            form.setData(
                                                'category_id',
                                                e.target.value === ''
                                                    ? ''
                                                    : Number(e.target.value),
                                            )
                                        }
                                    >
                                        <option value="">Uncategorised</option>
                                        {categories.map((c) => (
                                            <option key={c.id} value={c.id}>
                                                {c.name}
                                            </option>
                                        ))}
                                    </select>
                                </Field>

                                <Field label="Type" error={form.errors.type}>
                                    <select
                                        className="form-control capitalize"
                                        value={form.data.type}
                                        onChange={(e) =>
                                            form.setData('type', e.target.value)
                                        }
                                    >
                                        {types.map((t) => (
                                            <option key={t} value={t}>
                                                {t}
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                            </div>

                            <Field
                                label="Link / file URL"
                                error={form.errors.url}
                            >
                                <input
                                    className="form-control"
                                    placeholder="https://…  or  /files/guide.pdf"
                                    value={form.data.url}
                                    onChange={(e) =>
                                        form.setData('url', e.target.value)
                                    }
                                />
                            </Field>

                            <div>
                                <span className="block text-xs font-medium text-neutral-600">
                                    Image
                                </span>
                                <div className="mt-1.5">
                                    <MediaPicker
                                        api={mediaApi}
                                        kind="image"
                                        value={pathAsItem(form.data.image_path)}
                                        onChange={(item) =>
                                            form.setData(
                                                'image_path',
                                                item?.url ?? '',
                                            )
                                        }
                                        onError={(m) => toast.error(m)}
                                    />
                                </div>
                            </div>

                            <div className="flex items-center gap-6 pt-1">
                                <Toggle
                                    label="Featured"
                                    checked={form.data.is_featured}
                                    onChange={(v) =>
                                        form.setData('is_featured', v)
                                    }
                                />
                                <Toggle
                                    label="Published"
                                    checked={form.data.is_published}
                                    onChange={(v) =>
                                        form.setData('is_published', v)
                                    }
                                />
                            </div>
                        </form>

                        <div className="flex items-center justify-end gap-2 border-t border-black/10 px-5 py-3">
                            <button
                                type="button"
                                onClick={() => setOpen(false)}
                                className="btn-light"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                onClick={submit}
                                disabled={form.processing}
                                className="btn-black"
                            >
                                {editing ? 'Save changes' : 'Create'}
                            </button>
                        </div>
                    </DialogPanel>
                </div>
            </Dialog>

            <SideModal
                hidden={manageCats}
                onClose={() => setManageCats(false)}
                sizeClassName="xs:max-w-[420px]"
            >
                <span title="title" className="font-semibold">
                    Category tabs
                </span>
                <div title="body" data-slot="body" className="p-4">
                    <CategoryPanel
                        categories={categories}
                        confirm={setPending}
                    />
                </div>
            </SideModal>

            <ConfirmDialog
                open={pending !== null}
                title={pending?.title ?? ''}
                message={pending?.message}
                onConfirm={() => {
                    pending?.run();
                    setPending(null);
                }}
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
            <span className="block text-xs font-medium text-neutral-600">
                {label}
            </span>
            <div className="mt-1.5">{children}</div>
            {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
        </label>
    );
}

function Toggle({
    label,
    checked,
    onChange,
}: {
    label: string;
    checked: boolean;
    onChange: (v: boolean) => void;
}) {
    return (
        <label className="flex cursor-pointer items-center gap-2 text-sm">
            <input
                type="checkbox"
                checked={checked}
                onChange={(e) => onChange(e.target.checked)}
                className="size-4 rounded border-black/20"
            />
            {label}
        </label>
    );
}

function CategoryPanel({
    categories,
    confirm,
}: {
    categories: Category[];
    confirm: (p: Pending) => void;
}) {
    const [name, setName] = useState('');

    const add = (e: React.FormEvent) => {
        e.preventDefault();

        if (!name.trim()) {
            return;
        }

        router.post(
            '/admin/material-categories',
            { name },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setName('');
                    toast.success('Category added');
                },
            },
        );
    };

    const rename = (c: Category, value: string) => {
        if (value.trim() === '' || value === c.name) {
            return;
        }

        router.put(
            `/admin/material-categories/${c.id}`,
            { name: value },
            { preserveScroll: true, onSuccess: () => toast.success('Renamed') },
        );
    };

    const remove = (c: Category) =>
        confirm({
            title: `Delete “${c.name}”?`,
            message: 'Its materials become uncategorised.',
            run: () =>
                router.delete(`/admin/material-categories/${c.id}`, {
                    preserveScroll: true,
                    onSuccess: () => toast.success('Category deleted'),
                }),
        });

    return (
        <div>
            <p className="text-xs text-neutral-500">
                These are the filter tabs on the resources page. Rename inline;
                changes save when you click away.
            </p>

            <div className="mt-3 flex flex-wrap gap-2">
                {categories.map((c) => (
                    <div
                        key={c.id}
                        className="flex items-center gap-1.5 rounded-full border border-black/10 bg-white py-1 pr-1 pl-3"
                    >
                        <input
                            defaultValue={c.name}
                            onBlur={(e) => rename(c, e.target.value)}
                            className="w-28 bg-transparent text-xs focus:outline-none"
                        />
                        <span className="text-[10px] text-neutral-400">
                            {c.materialsCount}
                        </span>
                        <button
                            type="button"
                            onClick={() => remove(c)}
                            className="rounded-full p-0.5 text-neutral-400 hover:bg-red-50 hover:text-red-500"
                        >
                            <X className="size-3.5" />
                        </button>
                    </div>
                ))}
            </div>

            <form onSubmit={add} className="mt-3 flex items-center gap-2">
                <input
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    placeholder="New category"
                    className="form-control !py-1.5 !text-xs"
                />
                <button
                    type="submit"
                    className="btn-light inline-flex items-center gap-1 !py-1.5 !text-xs whitespace-nowrap"
                >
                    <Plus className="size-3.5" /> Add
                </button>
            </form>
        </div>
    );
}

MaterialsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Resources', href: '/admin/materials' },
    ],
};
