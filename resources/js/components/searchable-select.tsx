import {
    Listbox,
    ListboxButton,
    ListboxOption,
    ListboxOptions,
} from '@headlessui/react';
import { Calendar, Check, ChevronDown, Search } from 'lucide-react';
import { useEffect, useId, useRef, useState } from 'react';
import { appendQueryParam } from '@/hooks/useSupport';
import { cn } from '@/support/utils';

type SearchableSelectOption = {
    label: string;
    value: string;
    subtitle?: string;

    raw?: any;
};

type SearchableSelectProps = {
    label?: string;
    required?: boolean;
    placeholder?: string;
    searchPlaceholder?: string;
    options: SearchableSelectOption[];
    value: string | null;
    onChange: (value: string | null) => void;
    disabled?: boolean;
    className?: string;
    buttonClassName?: string;
    optionClassName?: string;
    emptyText?: string;
    /** AJAX endpoint — receives a `search` query param, must return `{ data: [...] }` */
    route?: string | null;
    /** Fetch an initial list from `route` on first render (when no local options) */
    preloadApi?: boolean;
    /** Key in each API response item to use as the display label. Default: "label" */
    labelKey?: string;
    /** Key in each API response item to use as the option value. Default: "value" */
    valueKey?: string;
    /** Key in each API response item to use as the subtitle line below the label */
    subtitleKey?: string;
    /** Custom render component for each option row — receives `{ item }` with the raw data */

    LineRender?: React.ComponentType<{ item: any }>;
};

export default function SearchableSelect({
    label,
    required = false,
    placeholder = 'Select an option',
    searchPlaceholder = 'Search...',
    options,
    value,
    onChange,
    disabled = false,
    className,
    buttonClassName,
    optionClassName,
    emptyText = 'No results found',
    route = null,
    preloadApi = false,
    labelKey = 'label',
    valueKey = 'value',
    subtitleKey,
    LineRender,
}: SearchableSelectProps) {
    const [query, setQuery] = useState('');
    const [remoteOptions, setRemoteOptions] = useState<
        SearchableSelectOption[]
    >([]);

    // Identifies the request the preload effect below will make. The effect must
    // not flip `loading` synchronously, so the flag is armed here instead: on
    // mount via the initial value, and on every later change of the key.
    const preloadKey = preloadApi && route !== null ? route : null;
    const [loading, setLoading] = useState(preloadKey !== null);
    const [armedPreloadKey, setArmedPreloadKey] = useState(preloadKey);

    if (armedPreloadKey !== preloadKey) {
        setArmedPreloadKey(preloadKey);
        setLoading(preloadKey !== null);
    }

    // Remembers the last picked option so the button keeps its label even after
    // remoteOptions is cleared (e.g. when the choice came from an AJAX result).
    const [selectedCache, setSelectedCache] =
        useState<SearchableSelectOption | null>(null);
    const throttleRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const inputId = useId();

    const allOptions = [...options, ...remoteOptions];
    const selectedOption =
        allOptions.find((opt) => opt.value === value) ??
        (selectedCache?.value === value ? selectedCache : null);

    const localFiltered =
        query === ''
            ? options
            : options.filter((opt) =>
                  opt.label.toLowerCase().includes(query.toLowerCase()),
              );

    const filteredOptions =
        localFiltered.length > 0 ? localFiltered : remoteOptions;

    // Pure fetch helper — each caller owns the `loading` flag around it, so that
    // calling this from an effect body never sets state synchronously.
    const callApi = async (
        keyword: string,
    ): Promise<SearchableSelectOption[]> => {
        try {
            const url = appendQueryParam(route as string, 'search', keyword);
            const response = await fetch(url, {
                headers: { 'Content-Type': 'application/json' },
            });
            const json = await response.json();

            const data: any[] = json?.data ?? [];

            return data.map((item) => ({
                label: String(item[labelKey] ?? ''),
                value: String(item[valueKey] ?? ''),
                ...(subtitleKey
                    ? { subtitle: String(item[subtitleKey] ?? '') }
                    : {}),
                raw: item,
            }));
        } catch {
            return [];
        }
    };

    // Seed an initial list from the endpoint on first render so the dropdown
    // isn't empty before the user types.
    useEffect(() => {
        if (!preloadApi || route === null) {
            return;
        }

        let active = true;
        callApi('').then((results) => {
            if (!active) {
                return;
            }

            setRemoteOptions(results);
            setLoading(false);
        });

        return () => {
            active = false;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [preloadApi, route]);

    const handleQueryChange = (keyword: string) => {
        setQuery(keyword);

        if (throttleRef.current) {
            clearTimeout(throttleRef.current);
            throttleRef.current = null;
        }

        const local =
            keyword === ''
                ? options
                : options.filter((opt) =>
                      opt.label.toLowerCase().includes(keyword.toLowerCase()),
                  );

        // Fetch on a real search (>=2 chars); when preloading, an empty query
        // re-fetches the default list so clearing the box restores it.
        const shouldFetch =
            local.length <= 0 &&
            route !== null &&
            (keyword.trim().length >= 2 || (preloadApi && keyword === ''));

        if (shouldFetch) {
            // Show the spinner immediately so the user gets feedback during the
            // debounce window, not just for the brief moment the request is in flight.
            setLoading(true);
            throttleRef.current = setTimeout(
                async () => {
                    const results = await callApi(keyword);
                    setRemoteOptions(results);
                    setLoading(false);
                    throttleRef.current = null;
                },
                keyword === '' ? 0 : 700,
            );
        } else {
            // No backend fetch will run — clear any pending spinner.
            setLoading(false);
            setRemoteOptions([]);
        }
    };

    const handleChange = (nextValue: string | null) => {
        // Capture the chosen option before remoteOptions is wiped, so the button
        // can still render its label.
        setSelectedCache(
            allOptions.find((opt) => opt.value === nextValue) ?? null,
        );
        onChange(nextValue);
        setQuery('');

        if (throttleRef.current) {
            clearTimeout(throttleRef.current);
            throttleRef.current = null;
        }

        setLoading(false);

        // When preloading, re-seed the default list so it isn't empty next time
        // the dropdown is opened; otherwise drop the transient search results.
        if (preloadApi && route !== null) {
            setLoading(true);
            callApi('').then((results) => {
                setRemoteOptions(results);
                setLoading(false);
            });
        } else {
            setRemoteOptions([]);
        }
    };

    return (
        <div className={className}>
            {label ? (
                <label htmlFor={inputId} className="mb-1 block text-sm">
                    {label}{' '}
                    {required ? <span className="text-red-500">*</span> : null}
                </label>
            ) : null}

            <Listbox
                value={selectedOption?.value ?? null}
                disabled={disabled}
                onChange={handleChange}
            >
                {({ open }) => (
                    <div className="relative">
                        <ListboxButton
                            id={inputId}
                            disabled={disabled}
                            className={cn(
                                'form-control flex w-full items-center justify-between gap-3 text-left',
                                !selectedOption && 'text-zinc-400',
                                disabled &&
                                    'cursor-not-allowed bg-zinc-100 text-zinc-400',
                                buttonClassName,
                            )}
                        >
                            <span className="truncate">
                                {selectedOption?.label ?? placeholder}
                            </span>
                            <ChevronDown
                                className={cn(
                                    'size-4 shrink-0 text-zinc-500 transition-transform',
                                    open && 'rotate-180',
                                )}
                            />
                        </ListboxButton>

                        <ListboxOptions
                            modal={false}
                            anchor={{ to: 'bottom start', gap: 4 }}
                            className="z-[100] max-h-[300px] w-[var(--button-width)] overflow-hidden rounded-md border border-zinc-200 bg-white shadow-lg focus:outline-none"
                        >
                            {/* Search input */}
                            <div className="border-b border-zinc-100 p-2">
                                <div className="flex items-center gap-2 rounded border border-zinc-200 px-3">
                                    <Search className="size-4 text-zinc-400" />
                                    <input
                                        ref={(el) =>
                                            el?.focus({ preventScroll: true })
                                        }
                                        value={query}
                                        onChange={(e) =>
                                            handleQueryChange(e.target.value)
                                        }
                                        placeholder={searchPlaceholder}
                                        className="w-full bg-transparent py-3 text-sm outline-none"
                                    />
                                    {loading && (
                                        <span className="shrink-0 rounded-full bg-zinc-300">
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
                                                />
                                                <path
                                                    className="opacity-60"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                />
                                            </svg>
                                        </span>
                                    )}
                                </div>
                            </div>

                            {/* Options list */}
                            <div className="max-h-48 overflow-y-auto py-1">
                                {filteredOptions.length > 0
                                    ? filteredOptions.map((option) => (
                                          <ListboxOption
                                              key={option.value}
                                              value={option.value}
                                              className={({ focus }) =>
                                                  cn(
                                                      'flex cursor-pointer items-center justify-between px-3 py-2 text-sm text-zinc-800 outline-none',
                                                      focus &&
                                                          'bg-[#FFF0F6] text-pink-600',
                                                      optionClassName,
                                                  )
                                              }
                                          >
                                              {({ selected }) => (
                                                  <>
                                                      {LineRender ? (
                                                          <LineRender
                                                              item={
                                                                  option.raw ??
                                                                  option
                                                              }
                                                          />
                                                      ) : (
                                                          <div className="flex min-w-0 flex-col">
                                                              <span className="truncate font-medium">
                                                                  {option.label}
                                                              </span>
                                                              {option.subtitle && (
                                                                  <span className="truncate text-xs text-zinc-400">
                                                                      {
                                                                          option.subtitle
                                                                      }
                                                                  </span>
                                                              )}
                                                          </div>
                                                      )}
                                                      {selected && (
                                                          <Check className="ml-2 size-4 shrink-0 text-pink-600" />
                                                      )}
                                                  </>
                                              )}
                                          </ListboxOption>
                                      ))
                                    : !loading && (
                                          <div className="flex flex-col items-center py-3">
                                              <Calendar className="size-6 text-zinc-400" />
                                              <div className="text-center text-sm font-medium text-zinc-400 capitalize">
                                                  {emptyText}
                                              </div>
                                          </div>
                                      )}
                            </div>
                        </ListboxOptions>
                    </div>
                )}
            </Listbox>
        </div>
    );
}
