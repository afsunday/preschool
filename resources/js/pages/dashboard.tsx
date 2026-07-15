import { Head } from '@inertiajs/react';
import { dashboard } from '@/routes';

export default function Dashboard() {
    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="relative aspect-video overflow-hidden rounded-[4px] border border-black/10 bg-black/[0.02]" />
                    <div className="relative aspect-video overflow-hidden rounded-[4px] border border-black/10 bg-black/[0.02]" />
                    <div className="relative aspect-video overflow-hidden rounded-[4px] border border-black/10 bg-black/[0.02]" />
                </div>
                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-[4px] border border-black/10 bg-black/[0.02] md:min-h-min" />
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
