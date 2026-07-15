import type { Auth } from '@/types/auth';

declare global {
    interface Window {
        // Legacy dropdown toggle helper used by some ported medplus components.
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        logicDropToggle?: (e: any) => void;
    }
}

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            [key: string]: unknown;
        };
    }
}
