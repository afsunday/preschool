import { ImagePlus, Loader2, X } from 'lucide-react';
import { useRef, useState } from 'react';

interface Pending {
    path: string;
    url: string;
    name: string;
}

/**
 * Pick photos; they upload the moment they're chosen.
 *
 * The wait overlaps with the teacher still typing, so submitting is instant —
 * the form posts the temp paths and the controller promotes them.
 *
 * Nothing to do with the media library: these are ordinary uploads, and a room's
 * daily photos have no business in the site's asset picker.
 */
export function PhotoUpload({
    value,
    onChange,
    max = 10,
}: {
    /** Temp paths chosen so far. */
    value: string[];
    onChange: (paths: string[]) => void;
    max?: number;
}) {
    const input = useRef<HTMLInputElement>(null);
    const [pending, setPending] = useState<Pending[]>([]);
    const [busy, setBusy] = useState(0);
    const [error, setError] = useState<string | null>(null);

    const upload = async (files: FileList) => {
        setError(null);
        const room = max - value.length;

        for (const file of Array.from(files).slice(0, room)) {
            setBusy((n) => n + 1);

            try {
                const body = new FormData();
                body.append('file', file);

                const res = await fetch('/portal/uploads', {
                    method: 'POST',
                    body,
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-XSRF-TOKEN': decodeURIComponent(
                            document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ??
                                '',
                        ),
                    },
                });

                if (!res.ok) {
                    const body = await res.json().catch(() => null);
                    setError(
                        body?.errors?.file?.[0] ??
                            body?.message ??
                            'That file could not be uploaded.',
                    );

                    continue;
                }

                const done: Pending = await res.json();
                setPending((p) => [...p, done]);
                onChange([...value, done.path]);
            } finally {
                setBusy((n) => n - 1);
            }
        }
    };

    const drop = (path: string) => {
        setPending((p) => p.filter((x) => x.path !== path));
        onChange(value.filter((p) => p !== path));
    };

    return (
        <div>
            <div className="flex flex-wrap items-center gap-2">
                {pending.map((photo) => (
                    <span key={photo.path} className="group relative">
                        <img
                            src={photo.url}
                            alt={photo.name}
                            className="size-16 rounded-[4px] object-cover"
                        />
                        <button
                            type="button"
                            onClick={() => drop(photo.path)}
                            aria-label={`Remove ${photo.name}`}
                            className="absolute -top-1.5 -right-1.5 grid size-5 place-items-center rounded-full bg-portal-ink text-white opacity-0 transition group-hover:opacity-100"
                        >
                            <X className="size-3" />
                        </button>
                    </span>
                ))}

                {busy > 0 && (
                    <span className="grid size-16 place-items-center rounded-[4px] bg-portal-field">
                        <Loader2 className="size-4 animate-spin text-neutral-400" />
                    </span>
                )}

                {value.length < max && (
                    <button
                        type="button"
                        onClick={() => input.current?.click()}
                        className="grid size-16 place-items-center rounded-[4px] border border-dashed border-portal-line text-neutral-400 transition hover:bg-portal-field hover:text-portal-ink"
                        aria-label="Add photos"
                    >
                        <ImagePlus className="size-5" />
                    </button>
                )}
            </div>

            <input
                ref={input}
                type="file"
                accept="image/*"
                multiple
                hidden
                onChange={(e) => {
                    if (e.target.files?.length) {
                        void upload(e.target.files);
                    }

                    // Let the same file be chosen again after removing it.
                    e.target.value = '';
                }}
            />

            {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
        </div>
    );
}
