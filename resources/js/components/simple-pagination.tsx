import { Link } from '@inertiajs/react';
import React from 'react';
import { pagingUrl } from '@/hooks/useSupport';

type PaginationProps = {
    pageable?: {
        current_page?: number;
        prev_page_url?: string | null;
        next_page_url?: string | null;
    };
    className?: string;
};

const SimplePagination: React.FC<PaginationProps> = ({
    pageable = {},
    ...props
}) => {
    return (
        <div className={`flex items-center justify-between ${props.className}`}>
            <div className="text-base font-medium text-zinc-700">
                Page {pageable?.current_page}
            </div>

            <div className="mt-2 inline-flex">
                <Link
                    prefetch={['mount', 'hover']}
                    cacheFor="5s"
                    href={
                        pageable?.prev_page_url
                            ? pagingUrl(pageable.prev_page_url)
                            : '#'
                    }
                    as={pageable?.prev_page_url == null ? 'button' : 'a'}
                    disabled={!pageable?.prev_page_url}
                    className="flex h-11 items-center justify-center rounded-s bg-black px-5 text-sm font-medium text-white hover:bg-neutral-900"
                >
                    Prev
                </Link>
                <Link
                    prefetch={['mount', 'hover']}
                    cacheFor="5s"
                    href={
                        pageable?.next_page_url
                            ? pagingUrl(pageable.next_page_url)
                            : '#'
                    }
                    as={pageable?.next_page_url == null ? 'button' : 'a'}
                    disabled={!pageable?.next_page_url}
                    className="flex h-11 items-center justify-center rounded-e border-0 border-s border-gray-700 bg-black px-5 text-sm font-medium text-white hover:bg-neutral-900 disabled:bg-neutral-400"
                >
                    Next
                </Link>
            </div>
        </div>
    );
};

export default SimplePagination;
