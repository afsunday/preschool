import { HTMLAttributes, PropsWithChildren } from 'react';

type EmptyStateProps = PropsWithChildren<{ colSpan?: number; title?: string; subtitle?: string }> & HTMLAttributes<HTMLTableCellElement>;

export default function TableEmpty({ colSpan = 9, title = 'No Records to Display', subtitle = '', ...props }: EmptyStateProps) {
    return (
        <tr>
            <td {...props} colSpan={colSpan}>
                <div className="flex flex-col items-center justify-center py-5">
                    <img src="/static/empty-state.png" className="h-28 w-28" alt="" />

                    <div className="mt-2 font-bold uppercase text-zinc-500">{title}</div>
                    <p className="text-sm">{subtitle}</p>
                </div>
            </td>
        </tr>
    );
}
