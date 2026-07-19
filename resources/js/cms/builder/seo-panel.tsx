import type { MediaItem } from '@/cms/media';
import { createHttpMediaApi, MediaPicker } from '@/cms/media';
import type { PageDoc } from './types';

const mediaApi = createHttpMediaApi('/admin/media/items');

export function SeoPanel({
    doc,
    onChange,
}: {
    doc: PageDoc;
    onChange: (patch: Partial<PageDoc>) => void;
}) {
    const setMeta = (patch: Partial<PageDoc['meta']>) =>
        onChange({ meta: { ...doc.meta, ...patch } });

    return (
        <div className="flex h-full flex-col">
            <div className="border-b border-black/10 px-3 py-2">
                <h2 className="text-sm font-semibold">SEO &amp; Scripts</h2>
            </div>

            <div className="flex-1 space-y-5 overflow-y-auto p-3">
                <section className="space-y-3">
                    <p className="text-xs font-semibold tracking-wide text-neutral-400 uppercase">
                        Search &amp; social
                    </p>

                    <label className="block">
                        <span className="mb-1 block text-xs font-medium text-neutral-600">
                            Meta title
                        </span>
                        <input
                            className="form-control"
                            value={doc.meta.title ?? ''}
                            placeholder={doc.title}
                            onChange={(e) =>
                                setMeta({ title: e.target.value || null })
                            }
                        />
                    </label>

                    <label className="block">
                        <span className="mb-1 block text-xs font-medium text-neutral-600">
                            Meta description
                        </span>
                        <textarea
                            className="form-control"
                            rows={3}
                            value={doc.meta.description ?? ''}
                            onChange={(e) =>
                                setMeta({ description: e.target.value || null })
                            }
                        />
                    </label>

                    <div className="block">
                        <span className="mb-1 block text-xs font-medium text-neutral-600">
                            Social share image
                        </span>
                        <OgImageField
                            value={doc.meta.ogImage ?? null}
                            onChange={(path) => setMeta({ ogImage: path })}
                        />
                    </div>
                </section>

                <section className="space-y-3">
                    <p className="text-xs font-semibold tracking-wide text-neutral-400 uppercase">
                        Custom code
                    </p>

                    <label className="block">
                        <span className="mb-1 block text-xs font-medium text-neutral-600">
                            Header scripts
                        </span>
                        <span className="mb-1 block text-[11px] text-neutral-400">
                            Injected into &lt;head&gt; — analytics, meta tags,
                            etc.
                        </span>
                        <textarea
                            className="form-control font-mono text-xs"
                            rows={5}
                            value={doc.headerScripts ?? ''}
                            onChange={(e) =>
                                onChange({
                                    headerScripts: e.target.value || null,
                                })
                            }
                            placeholder="<!-- e.g. <script>…</script> -->"
                        />
                    </label>

                    <label className="block">
                        <span className="mb-1 block text-xs font-medium text-neutral-600">
                            Footer scripts
                        </span>
                        <span className="mb-1 block text-[11px] text-neutral-400">
                            Injected before &lt;/body&gt; — chat widgets,
                            pixels, etc.
                        </span>
                        <textarea
                            className="form-control font-mono text-xs"
                            rows={5}
                            value={doc.footerScripts ?? ''}
                            onChange={(e) =>
                                onChange({
                                    footerScripts: e.target.value || null,
                                })
                            }
                            placeholder="<!-- e.g. <script>…</script> -->"
                        />
                    </label>
                </section>
            </div>
        </div>
    );
}

// A path is all we store; wrap it so the picker can show a thumbnail.
function pathAsItem(path: string | null): MediaItem | null {
    if (!path) {
        return null;
    }

    return {
        id: 0,
        url: path,
        kind: 'image',
        mimeType: null,
        originalName: path,
        title: null,
        alt: null,
        description: null,
        size: 0,
        width: null,
        height: null,
        createdAt: null,
    };
}

function OgImageField({
    value,
    onChange,
}: {
    value: string | null;
    onChange: (path: string | null) => void;
}) {
    return (
        <MediaPicker
            api={mediaApi}
            kind="image"
            value={pathAsItem(value)}
            onChange={(item) => onChange(item?.url ?? null)}
        />
    );
}
