import { Loader2, Search, X } from 'lucide-react';
import { forwardRef } from 'react';
import { cn } from '@/lib/utils';

type SearchBoxProps = {
    value: string;
    onChange: (value: string) => void;
    onEnter?: () => void;
    onClear?: () => void;
    placeholder?: string;
    loading?: boolean;
    className?: string;
};

/**
 * House-style search input (ported from medplus, lucide icons).
 */
const SearchBox = forwardRef<HTMLInputElement, SearchBoxProps>(
    function SearchBox(
        {
            value,
            onChange,
            onEnter,
            onClear,
            placeholder = 'Search',
            loading = false,
            className,
        },
        ref,
    ) {
        return (
            <div className={cn('relative min-w-[200px]', className)}>
                <span className="pointer-events-none absolute inset-y-0 left-3 flex items-center text-neutral-400">
                    {loading ? (
                        <Loader2 className="size-4 animate-spin" />
                    ) : (
                        <Search className="size-4" />
                    )}
                </span>
                <input
                    ref={ref}
                    type="search"
                    value={value}
                    placeholder={placeholder}
                    onChange={(e) => onChange(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && onEnter?.()}
                    className="w-full rounded-[4px] border border-black/10 bg-white py-2 pr-8 pl-9 text-sm outline-none focus:border-neutral-400"
                />
                {value.length > 0 && (
                    <button
                        type="button"
                        onClick={() => onClear?.()}
                        className="absolute inset-y-0 right-2 flex items-center text-neutral-400 hover:text-neutral-700"
                        aria-label="Clear"
                    >
                        <X className="size-4" />
                    </button>
                )}
            </div>
        );
    },
);

export default SearchBox;
