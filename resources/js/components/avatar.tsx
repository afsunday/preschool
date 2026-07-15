import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';

/**
 * Plain avatar (no Radix). Shows the image if present, else initials.
 */
export function Avatar({
    name,
    src,
    className,
}: {
    name: string;
    src?: string;
    className?: string;
}) {
    const getInitials = useInitials();

    return (
        <span
            className={cn(
                'inline-flex size-8 items-center justify-center overflow-hidden rounded-full bg-neutral-200 text-xs font-medium text-neutral-800',
                className,
            )}
        >
            {src ? (
                <img
                    src={src}
                    alt={name}
                    className="h-full w-full object-cover"
                />
            ) : (
                getInitials(name)
            )}
        </span>
    );
}
