import { Head, Link } from '@inertiajs/react';

const COPY: Record<number, { title: string; description: string }> = {
    403: {
        title: 'Not allowed',
        description:
            'You don’t have permission to view this page. If you think this is a mistake, contact the school.',
    },
    404: {
        title: 'Page not found',
        description:
            'The page you’re looking for doesn’t exist or may have moved.',
    },
    419: {
        title: 'Page expired',
        description: 'Your session timed out. Please refresh and try again.',
    },
    500: {
        title: 'Something went wrong',
        description:
            'A problem on our end stopped this page from loading. Please try again in a moment.',
    },
    503: {
        title: 'Down for maintenance',
        description:
            'We’re making some improvements. Please check back shortly.',
    },
};

export default function ErrorPage({ status }: { status: number }) {
    const { title, description } = COPY[status] ?? COPY[500];

    return (
        <>
            <Head title={title} />
            <div className="grid min-h-svh place-items-center bg-white px-6">
                <div className="w-full max-w-md text-center">
                    <p className="text-[6rem] leading-none font-extrabold tracking-tight text-[#ec1e79]">
                        {status}
                    </p>
                    <h1 className="mt-4 text-2xl font-bold text-neutral-900">
                        {title}
                    </h1>
                    <p className="mx-auto mt-2 max-w-sm text-neutral-500">
                        {description}
                    </p>

                    <div className="mt-8 flex items-center justify-center gap-3">
                        <Link
                            href="/"
                            className="rounded-[4px] bg-[#ec1e79] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#d1195f]"
                        >
                            Go home
                        </Link>
                        <button
                            type="button"
                            onClick={() => window.history.back()}
                            className="rounded-[4px] border border-black/10 px-5 py-2.5 text-sm font-semibold text-neutral-700 transition hover:bg-neutral-50"
                        >
                            Go back
                        </button>
                    </div>
                </div>
            </div>
        </>
    );
}
