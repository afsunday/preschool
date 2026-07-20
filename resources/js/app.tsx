import { createInertiaApp, router } from '@inertiajs/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { Toaster } from 'sonner';
import { NotificationProvider } from '@/hooks/notificationContext';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import PortalLayout from '@/layouts/portal-layout';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Never surface Inertia's raw HTML/error modal. In production, real errors render
// the branded error page (see bootstrap/app.php); this cancels the fallback modal
// for any stray non-Inertia response. The response is logged in dev so debugging
// isn't blind.
router.on('httpException', (event) => {
    event.preventDefault();

    if (import.meta.env.DEV) {
        console.error(
            '[inertia] suppressed non-Inertia response',
            event.detail,
        );
    }
});

const queryClient = new QueryClient({
    defaultOptions: {
        queries: { refetchOnWindowFocus: false, retry: 1 },
    },
});

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        // Error pages are full-screen with no app chrome.
        if (name.startsWith('errors/')) {
            return undefined;
        }

        if (name.startsWith('auth/')) {
            return AuthLayout;
        }

        // The parent/teacher-facing portal has its own chrome.
        if (name.startsWith('portal/')) {
            return PortalLayout;
        }

        // The page-builder editor is a full-screen app of its own.
        if (name === 'cms/page-editor') {
            return undefined;
        }

        return AppLayout;
    },
    strictMode: true,
    withApp(app) {
        return (
            <QueryClientProvider client={queryClient}>
                <NotificationProvider>
                    {app}
                    <Toaster richColors position="top-right" />
                </NotificationProvider>
            </QueryClientProvider>
        );
    },
    progress: {
        color: '#ec1e79',
        showSpinner: true,
    },
});
