import { CloudUpload } from 'lucide-react';
import { useState } from 'react';
import type { InputHTMLAttributes } from 'react';
import { useNotifications } from '@/hooks/notificationContext';
import type { Dynamic } from '@/types';

type LogicImagePickerType = {
    id?: string;
    value?: string | null;
    onChange: (data: Dynamic) => void;
    accept?: string;
    label?: string;
    divClassName?: string;
    path?: string;
    /** Endpoint the picked file is POSTed to. Defaults to the media items route. */
    uploadUrl?: string;
} & Omit<InputHTMLAttributes<HTMLInputElement>, 'onChange' | 'value'>;

export default function LogicImagePicker({
    id = 'logic-image',
    value,
    onChange,
    accept = '.png, .jpg, .webp, .jpeg',
    divClassName = '',
    label = 'Upload image',
    path = 'uploads',
    uploadUrl = '/admin/media/items',
    ...props
}: LogicImagePickerType) {
    const [isDropping, setIsDropping] = useState(false);
    const [isUploading, setIsUploading] = useState(false);
    const [modal, setModal] = useState(false);
    const { error } = useNotifications();

    const uploadFile = async (files: FileList | null) => {
        const file = files?.[0];

        if (!file) {
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('path', path);

        try {
            setIsUploading(true);
            const response = await fetch(uploadUrl, {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                throw new Error('Error uploading');
            }

            const payload = await response.json();
            onChange(payload);
        } catch {
            error('Sorry, an error occurred while uploading the image');
            onChange({});
        } finally {
            setIsUploading(false);
        }
    };

    return (
        <div>
            <div
                onDrop={(e) => {
                    e.preventDefault();
                    setIsDropping(false);
                    uploadFile(e.dataTransfer.files);
                }}
                onDragOver={(e) => {
                    e.preventDefault();
                    setIsDropping(true);
                }}
                onDragLeave={(e) => {
                    e.preventDefault();
                    setIsDropping(false);
                }}
                className={`relative flex flex-row items-center gap-x-3 rounded border bg-gray-50 px-2 py-2 md:gap-x-5 ${divClassName} ${isDropping ? '[&>*]:pointer-events-none' : ''}`}
            >
                {isDropping && (
                    <div className="absolute top-0 right-0 bottom-0 left-0 z-20 flex items-center justify-center bg-sky-100/50 opacity-90">
                        <span className="text-sm font-medium text-slate-600">
                            Release file to upload!
                        </span>
                    </div>
                )}
                <div className="relative h-[100px] w-[100px] overflow-hidden rounded border bg-white">
                    <div
                        onClick={() => value && setModal(true)}
                        className="h-[inherit] w-[inherit] bg-cover bg-center bg-no-repeat"
                        style={{
                            backgroundImage:
                                value && !isUploading
                                    ? `url(${value})`
                                    : 'none',
                        }}
                    ></div>
                    <div
                        className={`${isUploading ? 'absolute' : 'hidden'} top-0 right-0 bottom-0 left-0 z-20 flex flex-col items-center justify-center bg-black/10 backdrop-blur-[2px] transition-all duration-500`}
                    >
                        <div className="animate-bounce text-pink-600">
                            <CloudUpload className="size-5" />
                        </div>
                        <span className="block text-xs font-bold text-pink-600">
                            Uploading
                        </span>
                    </div>
                </div>
                <div>
                    <div className="flex flex-col gap-2">
                        <span className="text-sm text-slate-800 md:block">
                            {label}
                        </span>
                        <label
                            htmlFor={`logic-image-upload-${id}`}
                            className="focus:ring-primary-600 inline-flex w-max cursor-pointer rounded-sm border border-zinc-400 bg-white px-4 py-2 text-[13px] font-semibold whitespace-nowrap text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:outline-none"
                        >
                            <input
                                {...props}
                                type="file"
                                id={`logic-image-upload-${id}`}
                                className="hidden"
                                onChange={(e) => {
                                    uploadFile(e.target.files);
                                    e.target.value = '';
                                }}
                                accept={accept}
                                disabled={isUploading}
                            />
                            Browse file
                        </label>
                    </div>
                </div>
            </div>

            {value && modal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                    <div className="relative mx-4 max-w-3xl rounded-lg bg-white p-4 shadow-lg">
                        <button
                            onClick={() => setModal(false)}
                            className="absolute top-0 right-0 m-3 grid place-content-center rounded bg-black/60 px-2 py-1 font-bold text-white shadow-s1 backdrop-blur-sm"
                        >
                            Close
                        </button>
                        <img
                            src={value}
                            alt="Preview"
                            className="max-h-[80vh] w-full object-contain"
                        />
                    </div>
                </div>
            )}
        </div>
    );
}
