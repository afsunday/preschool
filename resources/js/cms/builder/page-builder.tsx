import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Layers, Loader2, Search } from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { cn } from '../lib/cn';
import { BlockTree } from './block-tree';
import { EditorFooter } from './editor-footer';
import { EditorTopbar } from './editor-topbar';
import { FieldPanel } from './field-panel';
import type { Device, PreviewMessage } from './preview-frame';
import { PreviewFrame } from './preview-frame';
import { SeoPanel } from './seo-panel';
import type { BuilderApi, PageDoc, PageBlock } from './types';

type Tab = 'design' | 'seo';

let tempId = -1; // client ids for unsaved blocks (server assigns real ones)

export function PageBuilder({
    api,
    pageId,
    pages = [],
    onError,
}: {
    api: BuilderApi;
    pageId: number;
    pages?: { id: number; title: string; slug: string }[];
    onError?: (message: string) => void;
}) {
    const qc = useQueryClient();
    const iframeRef = useRef<HTMLIFrameElement>(null);

    const schemaQuery = useQuery({
        // Keyed by page: the block types on offer are the page's own plus the
        // globals', not one shared list.
        queryKey: ['cms-schema', pageId],
        queryFn: () => api.schema(pageId),
        staleTime: Infinity,
    });
    const pageQuery = useQuery({
        queryKey: ['cms-page', pageId],
        queryFn: () => api.getPage(pageId),
    });

    const [doc, setDoc] = useState<PageDoc | null>(null);
    const [adopted, setAdopted] = useState<PageDoc | null>(null);
    const [selectedId, setSelectedId] = useState<number | null>(null);
    const [device, setDevice] = useState<Device>('desktop');
    const [tab, setTab] = useState<Tab>('design');
    const [previewHtml, setPreviewHtml] = useState<string | null>(null);
    const [renderedDoc, setRenderedDoc] = useState<PageDoc | null>(null);

    // Each freshly fetched page becomes the editable copy. Adjusted during
    // render (not in an effect) so no frame is ever committed with the previous
    // page's doc: https://react.dev/reference/react/useState#storing-information-from-previous-renders
    if (pageQuery.data && pageQuery.data !== adopted) {
        setAdopted(pageQuery.data);
        setDoc(pageQuery.data);
    }

    // Whole-page live preview: debounce-render the current doc → iframe srcdoc.
    useEffect(() => {
        if (!doc) {
            return;
        }

        // The debounce cannot cancel a request already in flight, and two
        // renders can resolve out of order. Ignore a superseded one entirely:
        // letting it through would paint a stale preview, and — because
        // `rendering` is derived from renderedDoc — would strand the spinner on
        // forever, since no further effect runs while the doc sits unchanged.
        let superseded = false;

        const t = setTimeout(async () => {
            try {
                const html = await api.renderPage(pageId, doc);

                if (!superseded) {
                    setPreviewHtml(html);
                }
            } catch (e) {
                if (!superseded) {
                    onError?.(
                        e instanceof Error ? e.message : 'Preview failed',
                    );
                }
            } finally {
                // Marks this doc as settled whether or not it rendered, so a
                // failed preview stops the spinner exactly as a success does.
                if (!superseded) {
                    setRenderedDoc(doc);
                }
            }
        }, 400);

        return () => {
            superseded = true;
            clearTimeout(t);
        };
    }, [doc, api, pageId, onError]);

    // Derived, not stored: a render is outstanding whenever the preview lags
    // the doc being edited.
    const rendering = doc !== null && renderedDoc !== doc;

    const postToFrame = useCallback((msg: Record<string, unknown>) => {
        iframeRef.current?.contentWindow?.postMessage(
            { source: 'cms-editor', ...msg },
            '*',
        );
    }, []);

    const saveMutation = useMutation({
        mutationFn: (d: PageDoc) => api.savePage(pageId, d),
        onSuccess: (fresh) => {
            setDoc(fresh);
            setSelectedId(null);
            qc.setQueryData(['cms-page', pageId], fresh);
        },
        onError: (e) =>
            onError?.(e instanceof Error ? e.message : 'Save failed'),
    });

    // ---- block operations: mutate the doc; the debounced render redraws ----
    const selectBlock = (id: number) => {
        setSelectedId(id);
        postToFrame({ type: 'select', id });
    };
    const deselect = () => {
        setSelectedId(null);
        postToFrame({ type: 'select', id: 0 });
    };

    const changeField = (key: string, value: unknown) => {
        if (!doc || selectedId == null) {
            return;
        }

        setDoc({
            ...doc,
            blocks: doc.blocks.map((s) =>
                s.id === selectedId
                    ? { ...s, settings: { ...s.settings, [key]: value } }
                    : s,
            ),
        });
    };

    const addBlock = (type: string) => {
        if (!doc) {
            return;
        }

        const schema = schemaQuery.data?.find((s) => s.key === type);
        const settings: Record<string, unknown> = {};
        schema?.fields.forEach((f) => {
            if (f.default !== undefined) {
                settings[f.id] = f.default;
            }
        });
        const block: PageBlock = {
            id: tempId--,
            type,
            position: doc.blocks.length,
            isVisible: true,
            settings,
        };
        setDoc({ ...doc, blocks: [...doc.blocks, block] });
        setSelectedId(block.id);
    };

    const removeBlock = (id: number) => {
        if (!doc) {
            return;
        }

        setDoc({ ...doc, blocks: doc.blocks.filter((s) => s.id !== id) });

        if (selectedId === id) {
            setSelectedId(null);
        }
    };

    const toggleVisible = (id: number) => {
        if (!doc) {
            return;
        }

        setDoc({
            ...doc,
            blocks: doc.blocks.map((s) =>
                s.id === id ? { ...s, isVisible: !s.isVisible } : s,
            ),
        });
    };

    const reorder = (from: number, to: number) => {
        if (!doc) {
            return;
        }

        const blocks = [...doc.blocks];
        const [moved] = blocks.splice(from, 1);
        blocks.splice(to, 0, moved);
        setDoc({ ...doc, blocks });
    };

    const save = () => {
        if (!doc) {
            return;
        }

        const blocks = doc.blocks.map((s, i) => ({ ...s, position: i }));
        saveMutation.mutate({ ...doc, blocks });
    };

    const onPreviewMessage = useCallback(
        (msg: PreviewMessage) => {
            if (msg.type === 'select' && msg.id != null) {
                setSelectedId(msg.id);
            }

            // Re-assert the current selection each time the frame reloads.
            if (msg.type === 'ready' && selectedId != null) {
                postToFrame({ type: 'select', id: selectedId });
            }
        },
        [selectedId, postToFrame],
    );

    const selectedBlock = useMemo(
        () => doc?.blocks.find((b) => b.id === selectedId) ?? null,
        [doc, selectedId],
    );
    const selectedSchema = useMemo(
        () =>
            schemaQuery.data?.find((s) => s.key === selectedBlock?.type) ??
            null,
        [schemaQuery.data, selectedBlock],
    );

    if (pageQuery.isPending || schemaQuery.isPending || !doc) {
        return (
            <div className="flex h-full items-center justify-center bg-neutral-50">
                <Loader2 className="size-6 animate-spin text-neutral-400" />
            </div>
        );
    }

    const toggleStatus = () =>
        setDoc({
            ...doc,
            status: doc.status === 'published' ? 'draft' : 'published',
        });

    return (
        <div className="flex h-full bg-neutral-50">
            <aside className="flex w-[320px] shrink-0 flex-col border-r border-black/10 bg-white">
                <EditorTopbar
                    title={doc.title}
                    onTitle={(v) => setDoc({ ...doc, title: v })}
                    pages={pages}
                    currentId={pageId}
                    device={device}
                    onDevice={setDevice}
                    previewHref={`/${doc.slug}`}
                    pagesHref="/admin/pages"
                />

                <div className="flex shrink-0 border-b border-black/10">
                    {(
                        [
                            ['design', 'Design', Layers],
                            ['seo', 'SEO', Search],
                        ] as [Tab, string, typeof Layers][]
                    ).map(([id, label, Icon]) => (
                        <button
                            key={id}
                            type="button"
                            onClick={() => setTab(id)}
                            className={cn(
                                'flex flex-1 items-center justify-center gap-1.5 border-b-2 py-2.5 text-sm font-medium transition',
                                tab === id
                                    ? 'border-neutral-900 text-neutral-900'
                                    : 'border-transparent text-neutral-400 hover:text-neutral-700',
                            )}
                        >
                            <Icon className="size-4" />
                            {label}
                        </button>
                    ))}
                </div>

                <div className="min-h-0 flex-1">
                    {tab === 'seo' ? (
                        <SeoPanel
                            doc={doc}
                            onChange={(patch) => setDoc({ ...doc, ...patch })}
                        />
                    ) : selectedBlock && selectedSchema ? (
                        <FieldPanel
                            block={selectedBlock}
                            schema={selectedSchema}
                            onChange={changeField}
                            onBack={deselect}
                        />
                    ) : (
                        <BlockTree
                            blocks={doc.blocks}
                            schemas={schemaQuery.data ?? []}
                            selectedId={selectedId}
                            onSelect={selectBlock}
                            onAdd={addBlock}
                            onRemove={removeBlock}
                            onToggleVisible={toggleVisible}
                            onReorder={reorder}
                        />
                    )}
                </div>

                <EditorFooter
                    status={doc.status}
                    onToggleStatus={toggleStatus}
                    onSave={save}
                    saving={saveMutation.isPending}
                    pagesHref="/admin/pages"
                />
            </aside>

            <PreviewFrame
                html={previewHtml}
                device={device}
                loading={rendering}
                iframeRef={iframeRef}
                onMessage={onPreviewMessage}
            />
        </div>
    );
}
