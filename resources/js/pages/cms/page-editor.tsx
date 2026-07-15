import { Head } from '@inertiajs/react';
import { toast } from 'sonner';
import { createHttpBuilderApi, PageBuilder } from '@/cms/builder';

const builderApi = createHttpBuilderApi('/admin/builder');

export default function PageEditor({ pageId }: { pageId: number }) {
    return (
        <>
            <Head title="Edit page" />
            <div className="h-screen w-screen overflow-hidden">
                <PageBuilder
                    api={builderApi}
                    pageId={pageId}
                    previewUrl={`/admin/builder/pages/${pageId}/preview?editor=1`}
                    onError={(m) => toast.error(m)}
                />
            </div>
        </>
    );
}
