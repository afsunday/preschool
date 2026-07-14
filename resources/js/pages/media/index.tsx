import { Head } from '@inertiajs/react';
import { toast } from 'sonner';
import { createHttpMediaApi, MediaLibrary } from '@/cms/media';
import { dashboard } from '@/routes';

const mediaApi = createHttpMediaApi('/admin/media/items');

export default function MediaIndex() {
    return (
        <>
            <Head title="Media" />

            <div className="flex h-[calc(100vh-8.5rem)] flex-1 flex-col gap-6 p-4">
                <div>
                    <h1 className="text-xl font-semibold tracking-tight">
                        Media Library
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Upload, search and manage images and files used across
                        the site.
                    </p>
                </div>

                <MediaLibrary
                    api={mediaApi}
                    mode="manage"
                    onError={(message) => toast.error(message)}
                />
            </div>
        </>
    );
}

MediaIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Media', href: '/admin/media' },
    ],
};
