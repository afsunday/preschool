import type { PropsWithChildren } from 'react';

type FooterActioProps = PropsWithChildren<
    {
        processing: boolean;
    } & React.ButtonHTMLAttributes<HTMLButtonElement>
>;

export default function LoadingButton({
    processing,
    children,
    ...props
}: FooterActioProps) {
    return (
        <button
            {...props}
            type="button"
            className={`btn-dark flex items-center justify-center gap-x-2 rounded py-2.5 text-center disabled:cursor-not-allowed ${props.className}`}
            disabled={processing}
        >
            {processing && (
                <div className="bouncing-ball">
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            )}
            <span className={`text-center ${processing ? 'hidden' : ''}`}>
                {children}
            </span>
        </button>
    );
}
