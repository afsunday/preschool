import { Loader2 } from 'lucide-react';

type FooterActioProps = {
    processing?: boolean;
    submit?: () => void;
    cancel: () => void;
    buttonWidthClassName?: string;
    submitType?: 'button' | 'submit';
    submitText?: string;
    cancelText?: string;
    disabled?: boolean;
};

export default function FooterAction({
    processing = false,
    submit = () => {},
    submitText = 'Submit',
    cancelText = 'Cancel',
    submitType = 'button',
    disabled = false,
    cancel,
    buttonWidthClassName = 'w-28',
}: FooterActioProps) {
    return (
        <div className="flex justify-end gap-x-3 px-3">
            <button
                onClick={cancel}
                type="button"
                className={`btn-light ${buttonWidthClassName} cursor-pointer rounded border border-neutral-300 py-2.5`}
                disabled={processing}
            >
                {cancelText}
            </button>
            <button
                type={submitType}
                onClick={submit}
                className={`btn-dark flex ${buttonWidthClassName} items-center justify-center gap-x-2 rounded py-2.5 text-center`}
                disabled={processing || disabled}
            >
                <Loader2
                    className={`size-4 animate-spin ${processing ? '' : 'hidden'}`}
                />
                <span className="text-center whitespace-nowrap">
                    {submitText}
                </span>
            </button>
        </div>
    );
}
