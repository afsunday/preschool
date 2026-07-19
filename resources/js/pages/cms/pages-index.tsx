import { Head, Link, router } from '@inertiajs/react';
import { DownloadCloud, Lock, Pencil, Trash2 } from 'lucide-react';
import PageBuilderController from '@/actions/App/Http/Controllers/PageBuilderController';

type PageRow = {
    id: number;
    title: string;
    slug: string;
    status: 'draft' | 'published';
    isSystem: boolean;
    sectionsCount: number;
    updatedAt: string | null;
};

export default function PagesIndex({ pages }: { pages: PageRow[] }) {
    return (
        <>
            <Head title="Pages" />

            <div className="flex h-full flex-col gap-6 p-4">
                <div className="flex items-end justify-between gap-4">
                    <div>
                        <h1 className="text-xl font-semibold tracking-tight">
                            Pages
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Build and manage the site's pages.
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={() =>
                                router.post(
                                    PageBuilderController.pull.url(),
                                    {},
                                    { preserveScroll: true },
                                )
                            }
                            className="btn-light inline-flex items-center gap-1.5 whitespace-nowrap"
                            title="Import new pages from the cms/pages blueprints"
                        >
                            <DownloadCloud className="size-4" /> Pull in new
                            pages
                        </button>

                        {/*
                          Page creation is disabled until the dynamic block/widget
                          concept exists — a blank page has no blocks to add, so it
                          would be un-editable. Pages come from the cms/pages
                          blueprints via "Pull in new pages" for now.

                        <Form
                            {...PageBuilderController.store.form()}
                            className="flex items-center gap-2"
                        >
                            {({ processing }) => (
                                <>
                                    <input
                                        name="title"
                                        required
                                        placeholder="New page title"
                                        className="form-control !py-2"
                                    />
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="btn-black inline-flex items-center gap-1.5 whitespace-nowrap"
                                    >
                                        <Plus className="size-4" /> Create
                                    </button>
                                </>
                            )}
                        </Form>
                        */}
                    </div>
                </div>

                <div className="overflow-hidden rounded-[4px] border border-black/10">
                    <table className="w-full text-sm">
                        <thead className="bg-neutral-50 text-left text-xs text-neutral-500 uppercase">
                            <tr>
                                <th className="px-4 py-2 font-medium">Title</th>
                                <th className="px-4 py-2 font-medium">Slug</th>
                                <th className="px-4 py-2 font-medium">
                                    Status
                                </th>
                                <th className="px-4 py-2 font-medium">
                                    Sections
                                </th>
                                <th className="px-4 py-2 font-medium">
                                    Updated
                                </th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-black/5">
                            {pages.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-neutral-400"
                                    >
                                        No pages yet. Create one above.
                                    </td>
                                </tr>
                            )}
                            {pages.map((page) => (
                                <tr
                                    key={page.id}
                                    className="hover:bg-neutral-50"
                                >
                                    <td className="px-4 py-2.5 font-medium">
                                        {page.title}
                                    </td>
                                    <td className="px-4 py-2.5 text-neutral-500">
                                        /{page.slug}
                                    </td>
                                    <td className="px-4 py-2.5">
                                        <span
                                            className={
                                                page.status === 'published'
                                                    ? 'rounded-[4px] bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700'
                                                    : 'rounded-[4px] bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600'
                                            }
                                        >
                                            {page.status}
                                        </span>
                                    </td>
                                    <td className="px-4 py-2.5 text-neutral-500">
                                        {page.sectionsCount}
                                    </td>
                                    <td className="px-4 py-2.5 text-neutral-500">
                                        {page.updatedAt}
                                    </td>
                                    <td className="px-4 py-2.5">
                                        <div className="flex items-center justify-end gap-1">
                                            <Link
                                                href={`/admin/pages/${page.id}/edit`}
                                                className="inline-flex items-center gap-1 rounded-[4px] px-2 py-1 text-neutral-600 hover:bg-neutral-100"
                                            >
                                                <Pencil className="size-4" />
                                                Edit
                                            </Link>
                                            {page.isSystem ? (
                                                <span
                                                    title="Default page — can't be deleted"
                                                    className="inline-flex items-center rounded-[4px] p-1 text-neutral-300"
                                                >
                                                    <Lock className="size-4" />
                                                </span>
                                            ) : (
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        if (
                                                            confirm(
                                                                `Delete “${page.title}”?`,
                                                            )
                                                        ) {
                                                            router.delete(
                                                                `/admin/pages/${page.id}`,
                                                            );
                                                        }
                                                    }}
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
        </>
    );
}
