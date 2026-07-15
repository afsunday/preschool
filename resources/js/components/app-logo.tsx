import { usePage } from '@inertiajs/react';

export default function AppLogo() {
    const { name } = usePage().props;

    return (
        <div className="grid flex-1 text-left text-sm">
            <span className="truncate leading-tight font-semibold">
                {name}
            </span>
        </div>
    );
}
