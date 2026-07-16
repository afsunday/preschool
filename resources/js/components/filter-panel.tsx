import { Filter } from 'lucide-react';
import type { ReactNode } from 'react';
import { ExtendedSelect } from './extended-select';
import Label from './label';

interface FilterPanelProps {
    filterCount: number;
    filters?: Record<string, unknown>;
    setFilter?: (name: string, value: unknown) => void;
    resetFilter: () => void;
    onApplyFilter: () => void;
    isLoading?: boolean;
    children: ReactNode;
}

export function FilterPanel({
    filterCount,
    resetFilter,
    onApplyFilter,
    isLoading,
    children,
}: FilterPanelProps) {
    return (
        <div className="panel-cover static">
            <button
                data-lc-toggle="dropdown"
                data-popper-placement="bottom-end"
                className="btn-dark relative flex items-center gap-x-1.5 rounded-[5px] px-3.5 !py-3"
            >
                <Filter className="size-4 text-white" />
                <span className="hidden sm:block">Filter</span>
                {filterCount > 0 && (
                    <span className="absolute -top-1 -right-1 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-blue-600 px-1 text-[10px] font-semibold text-white">
                        {filterCount}
                    </span>
                )}
            </button>
            <div className="dropdown-menu panel z-10 hidden rounded-md border border-neutral-200 bg-white bg-clip-padding sm:w-[350px]">
                <div className="flex items-center justify-between border-b border-neutral-300 bg-zinc-100 px-4 py-3 sm:border-b-0">
                    <span>Filter</span>
                </div>
                <div className="grid grid-cols-1 p-4">
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            onApplyFilter();
                        }}
                        className="grid grid-cols-2 gap-4 gap-x-2"
                    >
                        {children}

                        <div className="col-span-2 flex justify-end gap-x-3">
                            <button
                                className="btn-light"
                                disabled={isLoading}
                                onClick={() => {
                                    resetFilter();
                                    document.body.click();
                                }}
                                type="button"
                            >
                                Reset
                            </button>
                            <button
                                disabled={isLoading}
                                type="submit"
                                className="btn-dark px-5"
                            >
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}

interface FilterInputProps extends Omit<
    React.InputHTMLAttributes<HTMLInputElement>,
    'onChange'
> {
    name: string;
    label: string;
    type?: string;
    value: string;
    onChange: (name: string, value: string) => void;
    placeholder?: string;
    className?: string;
    wrapClassName?: string;
}

export function FilterInput({
    name,
    label,
    type = 'text',
    value,
    onChange,
    placeholder,
    className = 'form-control',
    wrapClassName = 'col-span-1',
    ...props
}: FilterInputProps) {
    return (
        <div className={wrapClassName}>
            <Label htmlFor={name} text={label} />
            <input
                {...props}
                type={type}
                id={name}
                value={value}
                onChange={(e) => onChange(name, e.target.value)}
                className={className}
                placeholder={placeholder}
            />
        </div>
    );
}

interface FilterSelectProps {
    name: string;
    label: string;
    colspan?: 1 | 2;
    value: string;
    onChange: (name: string, value: string) => void;
    loading?: boolean;
    className?: string;
    wrapClassName?: string;
    children: ReactNode;
}

export function FilterSelect({
    name,
    label,
    value,
    onChange,
    loading = false,
    className = 'form-select form-control',
    wrapClassName = 'col-span-1',
    children,
}: FilterSelectProps) {
    return (
        <div className={wrapClassName}>
            <Label htmlFor={name} text={label} />
            <ExtendedSelect
                id={name}
                className={className}
                value={value}
                onChange={(e) => onChange(name, e.target.value)}
                loading={loading}
            >
                {children}
            </ExtendedSelect>
        </div>
    );
}

interface FilterGridProps {
    name: string;
    label: string;
    wrapClassName?: string;
    children: ReactNode;
}

export function FilterGrid({
    name,
    label,
    wrapClassName = 'col-span-1',
    children,
}: FilterGridProps) {
    return (
        <div className={wrapClassName}>
            <Label htmlFor={name} text={label} />
            {children}
        </div>
    );
}
