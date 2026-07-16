import { Calendar } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { appendQueryParam } from '@/hooks/useSupport';
import type { Dynamic } from '@/types';

type SelectType = {
    data: Dynamic[] | Dynamic;
    value?: Dynamic | null;
    optionValue: string;
    label: string;
    placeholder?: string;
    searchPlaceholder?: string;
    id: string;
    route?: string | null;
    required?: boolean;
    selectClassname?: string;
    dropClassname?: string;
    lineClassname?: string;
    searchClassname?: string;
    className?: string;
    LineRender?: React.ComponentType<{ item: Dynamic }>;
    onChange: (value: Dynamic | null) => void;
    preloadApi?: boolean;
    noRecordText?: string | React.ReactNode;
    noRecordActionText?: string;
    noRecordActionClick?: (e: React.MouseEvent<HTMLButtonElement>) => void;
};

export default function LogicSelect({
    data,
    optionValue,
    label,
    value = null,
    onChange,
    placeholder = 'Select item',
    searchPlaceholder = 'Search',
    id,
    route = null,
    required = false,
    selectClassname = '',
    dropClassname = '',
    lineClassname = '',
    searchClassname = '',
    className = '',
    LineRender,
    preloadApi = false,
    noRecordText = 'No Record found',
    noRecordActionText,
    noRecordActionClick,
}: SelectType) {
    const [selected, setSelected] = useState<Dynamic | null>(null);
    const [query, setQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [filteredData, setFilteredData] = useState<Dynamic[]>([]);
    const searchInput = useRef<HTMLInputElement>(null);
    const throttleRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const handleSelected = (item: Dynamic | null) => {
        setSelected(item);
        onChange(item);

        document.body.click();
    };

    useEffect(() => {
        if (!preloadApi) {
            return;
        }

        const preload = async () => {
            const res = await callApi();
            setFilteredData([...res]);
        };

        preload();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        setSelected(value);
    }, [value]);

    useEffect(() => {
        if (data == undefined) {
            setLoading(true);
        } else {
            setFilteredData([...(Array.isArray(data) ? data : [])]);
            setLoading(false);
        }
    }, [data]);

    const handleFiltering = async (keyword: string) => {
        setQuery(keyword);

        if (throttleRef.current) {
            clearTimeout(throttleRef.current);
            throttleRef.current = null;
        }

        const filtered =
            keyword === ''
                ? Array.isArray(data)
                    ? data
                    : []
                : data?.filter((datum: Dynamic) => {
                      return datum[label]
                          .toLowerCase()
                          .includes(keyword.toLowerCase());
                  });

        if (
            filtered?.length <= 0 &&
            route !== null &&
            keyword.trim().length >= 2
        ) {
            if (throttleRef.current == null) {
                throttleRef.current = setTimeout(async () => {
                    const res = await callApi(keyword);
                    setFilteredData([...res]);
                    throttleRef.current = null;
                }, 700);
            }
        } else {
            setFilteredData(filtered);
        }
    };

    const callApi = async (keyword?: string): Promise<Dynamic[]> => {
        try {
            setLoading(true);

            const url = appendQueryParam(route, 'search', keyword);

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            const { data } = await response.json();

            return data;
        } catch (error) {
            console.error('Error occurred:', error);

            return [];
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className={`relative ${className}`}>
            <div
                className={`form-control form-select relative flex flex-col ${selectClassname}`}
                data-lc-target={id}
                data-lc-match-width
                data-lc-toggle="dropdown"
                data-popper-placement="bottom-end"
                onClick={() =>
                    setTimeout(() => searchInput?.current?.focus(), 100)
                }
            >
                <small className="truncate text-sm">
                    {selected?.[label] ?? placeholder}
                </small>
                <input
                    type="text"
                    value={selected?.[label] ?? ''}
                    onChange={() => ''}
                    className="h-[1px] w-full opacity-0"
                    required={required}
                />
            </div>

            {createPortal(
                <div
                    id={id}
                    className={`dropdown-menu z-[100] hidden rounded-md border border-neutral-200 bg-white ${dropClassname}`}
                >
                    <div className="px-2 pt-3 pb-2">
                        <div className="relative flex items-center">
                            <input
                                ref={searchInput}
                                type="text"
                                placeholder={searchPlaceholder}
                                className={`form-control bg-transparent ${searchClassname} ${loading ? 'pr-10' : ''}`}
                                onChange={(e) =>
                                    handleFiltering(e?.target.value)
                                }
                                onKeyUp={(e) =>
                                    e.key === 'Enter' && handleFiltering(query)
                                }
                                autoFocus={true}
                            />
                            {loading && (
                                <span className="absolute right-0 mr-3 rounded-full bg-zinc-300">
                                    <Circle />
                                </span>
                            )}
                        </div>
                    </div>
                    <div className="scrollbar-sm flex max-h-[200px] flex-col overflow-auto">
                        <span className="w-full">
                            <input
                                id={`lx-unselected-${id}`}
                                onChange={() => handleSelected(null)}
                                type="radio"
                                name={`lc-unselect-name${id}`}
                                value={value?.[optionValue] ?? ''}
                                checked={selected === null}
                                className="peer hidden"
                            />
                            <label
                                htmlFor={`lx-unselected-${id}`}
                                className="block border-0 px-4 py-2 text-left text-sm peer-checked:bg-neutral-200 hover:bg-neutral-100"
                            >
                                <span>{placeholder}</span>
                            </label>
                        </span>
                        {filteredData?.map((item: Dynamic, index: number) => (
                            <span key={index} className="w-full">
                                <input
                                    id={`logic-select-${id}-${index}`}
                                    onChange={() => handleSelected(item)}
                                    type="radio"
                                    name={`lc-items-name${id}`}
                                    checked={
                                        selected?.[optionValue] ==
                                        item[optionValue]
                                    }
                                    className="peer hidden"
                                />
                                <label
                                    htmlFor={`logic-select-${id}-${index}`}
                                    className={`block border-0 px-4 py-2 text-left text-sm peer-checked:bg-neutral-200 hover:bg-neutral-100 ${lineClassname}`}
                                >
                                    {LineRender ? (
                                        <LineRender item={item} />
                                    ) : (
                                        <span>{item[label]}</span>
                                    )}
                                </label>
                            </span>
                        ))}

                        {filteredData?.length <= 0 && !loading && (
                            <div className="flex flex-col items-center py-3">
                                <Calendar className="size-6 text-zinc-400" />
                                <div className="text-center text-sm font-medium text-zinc-400 capitalize">
                                    {noRecordText}
                                </div>
                                {noRecordActionClick && (
                                    <button
                                        onClick={(e) => noRecordActionClick(e)}
                                        className="btn-pink my-2 !cursor-pointer !py-1.5"
                                    >
                                        {noRecordActionText}
                                    </button>
                                )}
                            </div>
                        )}
                    </div>
                </div>,
                document.body,
            )}
        </div>
    );
}

const Circle = () => {
    return (
        <svg
            className="h-5 w-5 animate-spin text-zinc-700"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                className="opacity-30"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                strokeWidth="4"
            ></circle>
            <path
                className="opacity-60"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>
    );
};
