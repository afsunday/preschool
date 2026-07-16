import type { PropsWithChildren } from 'react';

type EmptyStateProps = PropsWithChildren<{
    title?: string;
    subtitle?: string;
    className?: string;
    small?: boolean;
}>;

export default function EmptyState({
    title = 'No Records to Display',
    small = false,
    subtitle = '',
    ...props
}: EmptyStateProps) {
    return (
        <div className={`flex justify-center ${props.className}`}>
            <div className="flex flex-col items-center justify-center py-5">
                <img
                    src="/static/empty-state.png"
                    className={`${small ? 'h-20 w-20' : 'h-28 w-28'}`}
                    alt=""
                />

                <div className="mt-2 text-xs font-bold text-zinc-500 uppercase">
                    {title}
                </div>
                <p className="text-sm">{subtitle}</p>
            </div>
        </div>
    );
}
