import { Head } from '@inertiajs/react';
import { toast } from 'sonner';
import { createHttpBuilderApi, PageBuilder } from '@/cms/builder';

const builderApi = createHttpBuilderApi('/admin/builder');

type PageRef = { id: number; title: string; slug: string };

export default function PageEditor({
    pageId,
    pages,
}: {
    pageId: number;
    pages: PageRef[];
}) {
    return (
        <>
            <Head title="Edit page" />
            <div className="h-screen w-screen overflow-hidden">
                <PageBuilder
                    api={builderApi}
                    pageId={pageId}
                    pages={pages}
                    onError={(m) => toast.error(m)}
                />
            </div>
        </>
    );
}
