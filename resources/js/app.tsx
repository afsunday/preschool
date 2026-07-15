import { createInertiaApp } from '@inertiajs/react';
import { Toaster } from 'sonner';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import { NotificationProvider } from '@/hooks/notificationContext';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) =>
        name.startsWith('auth/') ? AuthLayout : AppLayout,
    strictMode: true,
    withApp(app) {
        return (
            <NotificationProvider>
                {app}
                <Toaster richColors position="top-right" />
            </NotificationProvider>
        );
    },
    progress: {
        color: '#111111',
    },
});
