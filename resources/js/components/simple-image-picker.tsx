import { CloudUpload } from 'lucide-react';
import { InputHTMLAttributes, useState } from 'react';

type SimpleImagePickerType = {
    id?: string;
    handleDrop: (e: React.DragEvent<HTMLDivElement>) => void;
    onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
    accept?: string;
    uploading: boolean;
    label?: string;
    divClassName?: string;
    imagePath?: string | null;
} & InputHTMLAttributes<HTMLInputElement>;

export default function SimpleImagePicker({
    id = 'simple',
    handleDrop,
    onChange,
    uploading,
    accept = '.png, .jpg, .webp, .jpeg',
    divClassName = '',
    label = 'Upload product image',
    imagePath,
    ...props
}: SimpleImagePickerType) {
    const [isDropping, setIsDropping] = useState(false);
    const [modal, setModal] = useState(false);

    return (
        <div>
            <div
                onDrop={(e) => {
                    e.preventDefault();
                    setIsDropping(false);
                    handleDrop(e);
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
                    <div className="absolute bottom-0 left-0 right-0 top-0 z-20 flex items-center justify-center bg-sky-100/50 opacity-90">
                        <span className="text-sm font-medium text-slate-600">Release file to upload!</span>
                    </div>
                )}
                <div className="relative h-[100px] w-[100px] overflow-hidden rounded border bg-white">
                    <div
                        onClick={() => imagePath && setModal(true)}
                        className="h-[inherit] w-[inherit] bg-cover bg-no-repeat"
                        style={{
                            backgroundImage: imagePath && !uploading ? `url(${imagePath})` : 'none',
                        }}
                    ></div>
                    <div
                        className={`${uploading ? 'absolute' : 'hidden'} bottom-0 left-0 right-0 top-0 z-20 flex flex-col items-center justify-center bg-black/10 backdrop-blur-[2px] transition-all duration-500`}
                    >
                        <div className="animate-bounce text-pink-600">
                            <CloudUpload className="size-5" />
                        </div>
                        <span className="block text-xs font-bold text-pink-600">Uploading</span>
                    </div>
                </div>
                <div className="">
                    <div className="flex flex-col gap-2">
                        <span className="text-sm text-slate-800 md:block">{label}</span>
                        <label
                            id={`image-upload-${id}`}
                            className="focus:ring-primary-600 inline-flex w-max whitespace-nowrap rounded-sm border border-zinc-400 bg-white px-4 py-2 text-[13px] font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
                        >
                            <input
                                {...props}
                                type="file"
                                id={`image-upload-${id}`}
                                className="hidden"
                                onChange={(e) => {
                                    onChange(e);
                                    e.target.value = '';
                                }}
                                accept={accept}
                                disabled={uploading}
                            />
                            Browse file
                        </label>
                    </div>
                </div>
            </div>

            {imagePath && modal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                    <div className="relative mx-4 max-w-3xl rounded-lg bg-white p-4 shadow-lg">
                        <button
                            onClick={() => setModal(false)}
                            className="absolute right-0 top-0 m-3 grid place-content-center rounded bg-black/60 px-2 py-1 font-bold text-white shadow-s1 backdrop-blur-sm"
                        >
                            Close
                        </button>
                        <img src={imagePath} alt="Preview" className="max-h-[80vh] w-full object-contain" />
                    </div>
                </div>
            )}
        </div>
    );
}
