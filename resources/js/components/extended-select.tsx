import type { ReactNode, SelectHTMLAttributes } from 'react';
import { Circle } from './atom';

interface ExtendedSelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
    children: ReactNode;
    loading?: boolean;
}

export const ExtendedSelect = ({
    children,
    loading = false,
    ...props
}: ExtendedSelectProps) => {
    return (
        <div className="relative">
            <select {...props} className={`${props.className}`}>
                {children}
            </select>
            {loading && (
                <span className="absolute top-1/4 right-0 mr-3 rounded-full bg-zinc-300">
                    <Circle />
                </span>
            )}
        </div>
    );
};
