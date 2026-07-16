import { CloudUpload } from 'lucide-react';
import type { ChangeEvent, DragEvent, InputHTMLAttributes } from 'react';
import { useState } from 'react';
import { cn } from '@/lib/utils';

type FilePickerProps = {
    onFiles: (files: FileList) => void;
    uploading?: boolean;
    accept?: string;
    multiple?: boolean;
    className?: string;
} & Omit<InputHTMLAttributes<HTMLInputElement>, 'onChange' | 'type'>;

/**
 * Drag-and-drop file target (ported from medplus, lucide icons).
 */
export default function FilePicker({
    onFiles,
    uploading = false,
    accept = '.png,.jpg,.jpeg,.webp,.pdf,.doc,.docx,.xlsx,.csv',
    multiple = true,
    className,
    ...props
}: FilePickerProps) {
    const [dropping, setDropping] = useState(false);

    const handleDrop = (e: DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        setDropping(false);

        if (e.dataTransfer.files.length) {
            onFiles(e.dataTransfer.files);
        }
    };

    const handleBrowse = (e: ChangeEvent<HTMLInputElement>) => {
        if (e.target.files?.length) {
            onFiles(e.target.files);
        }

        e.target.value = '';
    };

    return (
        <div
            onDrop={handleDrop}
            onDragOver={(e) => {
                e.preventDefault();
                setDropping(true);
            }}
            onDragLeave={(e) => {
                e.preventDefault();
                setDropping(false);
            }}
            className={cn(
                'relative grid place-items-center rounded-[4px] border border-dashed bg-neutral-50 py-10 transition',
                dropping
                    ? 'border-neutral-500 bg-neutral-100'
                    : 'border-black/15',
                className,
            )}
        >
            <div className="flex flex-col items-center gap-2 text-center">
                <CloudUpload
                    className="size-7 text-neutral-400"
                    strokeWidth={1.5}
                />
                <span className="text-sm text-neutral-600">
                    Drag and drop files, or
                </span>
                <label className="btn-light cursor-pointer text-xs">
                    Browse files
                    <input
                        {...props}
                        type="file"
                        className="hidden"
                        accept={accept}
                        multiple={multiple}
                        onChange={handleBrowse}
                    />
                </label>
            </div>

            {(dropping || uploading) && (
                <div className="absolute inset-0 grid place-items-center rounded-[4px] bg-white/70 text-sm font-medium text-neutral-700 backdrop-blur-[1px]">
                    {uploading ? 'Uploading…' : 'Drop to upload'}
                </div>
            )}
        </div>
    );
}
