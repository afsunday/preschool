import { CircleHelp } from 'lucide-react';
import { ReactNode } from 'react';

export default function Label({
    text,
    htmlFor,
    required = false,
    tooltip = null,
    className = '',
    tooltipPosition = 'bottom',
}: {
    text: string | ReactNode;
    htmlFor?: string;
    required?: boolean;
    tooltip?: string | null;
    className?: string;
    tooltipPosition?:
        | string
        | 'bottom'
        | 'top'
        | 'right'
        | 'left'
        | 'bottom-start'
        | 'bottom-end'
        | 'top-start'
        | 'top-end'
        | 'right-start'
        | 'right-end'
        | 'left-start'
        | 'left-end';
}) {
    return (
        <label htmlFor={htmlFor} className={`mb-1 block text-sm ${tooltip ? 'flex items-center gap-x-1' : ''} ${className}`}>
            {text} {required && <span className="text-base font-bold leading-[0] text-red-600">*</span>}
            {tooltip && (
                <div className="relative">
                    <code
                        onMouseOver={(e) => window.logicDropToggle?.(e)}
                        onMouseLeave={(e) => window.logicDropToggle?.(e)}
                        className="text cursor-pointer"
                        data-lc-toggle="dropdown"
                        data-popper-placement={tooltipPosition}
                    >
                        <CircleHelp className="size-4 text-gray-600" />
                    </code>
                    <div className="dropdown-menu z-10 hidden w-[200px] rounded-md border border-neutral-100 bg-[#262a33] bg-clip-padding px-3 py-4">
                        <p className="text-sm text-slate-200" dangerouslySetInnerHTML={{ __html: tooltip }}></p>
                    </div>
                </div>
            )}
        </label>
    );
}
