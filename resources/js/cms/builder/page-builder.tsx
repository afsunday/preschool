import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Loader2 } from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { EditorTopbar } from './editor-topbar';
import { FieldPanel } from './field-panel';
import { Device, PreviewFrame, PreviewMessage } from './preview-frame';
import { SectionTree } from './section-tree';
import { BuilderApi, PageDoc, SectionInstance } from './types';

let tempId = -1; // client ids for unsaved sections (server assigns real ones)

export function PageBuilder({
    api,
    pageId,
    previewUrl,
    onError,
}: {
    api: BuilderApi;
    pageId: number;
    previewUrl: string;
    onError?: (message: string) => void;
}) {
    const qc = useQueryClient();
    const iframeRef = useRef<HTMLIFrameElement>(null);

    const schemaQuery = useQuery({
        queryKey: ['cms-schema'],
        queryFn: () => api.schema(),
        staleTime: Infinity,
    });
    const pageQuery = useQuery({
        queryKey: ['cms-page', pageId],
        queryFn: () => api.getPage(pageId),
    });

    const [doc, setDoc] = useState<PageDoc | null>(null);
    const [selectedId, setSelectedId] = useState<number | null>(null);
    const [device, setDevice] = useState<Device>('desktop');
    const [frameReady, setFrameReady] = useState(false);

    useEffect(() => {
        if (pageQuery.data) setDoc(pageQuery.data);
    }, [pageQuery.data]);

    const post = useCallback((msg: Record<string, unknown>) => {
        iframeRef.current?.contentWindow?.postMessage(
            { source: 'cms-editor', ...msg },
            '*',
        );
    }, []);

    const renderTimers = useRef<Record<number, ReturnType<typeof setTimeout>>>({});
    const liveRender = useCallback(
        (section: SectionInstance) => {
            clearTimeout(renderTimers.current[section.id]);
            renderTimers.current[section.id] = setTimeout(async () => {
                try {
                    const html = await api.renderSection(
                        section.type,
                        section.settings,
                    );
                    post({ type: 'update', id: section.id, html });
                } catch (e) {
                    onError?.(e instanceof Error ? e.message : 'Render failed');
                }
            }, 300);
        },
        [api, post, onError],
    );

    const saveMutation = useMutation({
        mutationFn: (d: PageDoc) => api.savePage(pageId, d),
        onSuccess: (fresh) => {
            setDoc(fresh);
            setSelectedId(null);
            qc.setQueryData(['cms-page', pageId], fresh);
            if (iframeRef.current) iframeRef.current.src = previewUrl;
        },
        onError: (e) =>
            onError?.(e instanceof Error ? e.message : 'Save failed'),
    });

    const selectSection = (id: number) => {
        setSelectedId(id);
        post({ type: 'select', id });
    };
    const deselect = () => {
        setSelectedId(null);
        post({ type: 'select', id: 0 });
    };

    const changeField = (key: string, value: unknown) => {
        if (!doc || selectedId == null) return;
        const next = {
            ...doc,
            sections: doc.sections.map((s) =>
                s.id === selectedId
                    ? { ...s, settings: { ...s.settings, [key]: value } }
                    : s,
            ),
        };
        setDoc(next);
        const section = next.sections.find((s) => s.id === selectedId);
        if (section) liveRender(section);
    };

    const addSection = async (type: string) => {
        if (!doc) return;
        const schema = schemaQuery.data?.find((s) => s.key === type);
        const settings: Record<string, unknown> = {};
        schema?.fields.forEach((f) => {
            if (f.default !== undefined) settings[f.id] = f.default;
        });
        const section: SectionInstance = {
            id: tempId--,
            type,
            position: doc.sections.length,
            isVisible: true,
            settings,
            children: [],
        };
        setDoc({ ...doc, sections: [...doc.sections, section] });
        setSelectedId(section.id);
        try {
            const html = await api.renderSection(type, settings);
            post({ type: 'insert', id: section.id, html });
        } catch (e) {
            onError?.(e instanceof Error ? e.message : 'Render failed');
        }
    };

    const removeSection = (id: number) => {
        if (!doc) return;
        setDoc({ ...doc, sections: doc.sections.filter((s) => s.id !== id) });
        if (selectedId === id) setSelectedId(null);
        post({ type: 'remove', id });
    };

    const toggleVisible = (id: number) => {
        if (!doc) return;
        setDoc({
            ...doc,
            sections: doc.sections.map((s) =>
                s.id === id ? { ...s, isVisible: !s.isVisible } : s,
            ),
        });
    };

    const reorder = (from: number, to: number) => {
        if (!doc) return;
        const sections = [...doc.sections];
        const [moved] = sections.splice(from, 1);
        sections.splice(to, 0, moved);
        setDoc({ ...doc, sections });
        post({ type: 'reorder', order: sections.map((s) => s.id) });
    };

    const save = () => {
        if (!doc) return;
        const sections = doc.sections.map((s, i) => ({ ...s, position: i }));
        saveMutation.mutate({ ...doc, sections });
    };

    const onPreviewMessage = useCallback((msg: PreviewMessage) => {
        if (msg.type === 'ready') setFrameReady(true);
        if (msg.type === 'select' && msg.id != null) setSelectedId(msg.id);
    }, []);

    const selectedSection = useMemo(
        () => doc?.sections.find((s) => s.id === selectedId) ?? null,
        [doc, selectedId],
    );
    const selectedSchema = useMemo(
        () =>
            schemaQuery.data?.find((s) => s.key === selectedSection?.type) ??
            null,
        [schemaQuery.data, selectedSection],
    );

    if (pageQuery.isPending || schemaQuery.isPending || !doc) {
        return (
            <div className="flex h-full items-center justify-center bg-neutral-50">
                <Loader2 className="size-6 animate-spin text-neutral-400" />
            </div>
        );
    }

    return (
        <div className="flex h-full flex-col bg-neutral-50">
            <EditorTopbar
                title={doc.title}
                onTitle={(v) => setDoc({ ...doc, title: v })}
                status={doc.status}
                onToggleStatus={() =>
                    setDoc({
                        ...doc,
                        status:
                            doc.status === 'published' ? 'draft' : 'published',
                    })
                }
                device={device}
                onDevice={setDevice}
                onSave={save}
                saving={saveMutation.isPending || !frameReady}
                previewHref={previewUrl.replace(/\?editor=1$/, '')}
                pagesHref="/admin/pages"
            />

            <div className="flex min-h-0 flex-1">
                <aside className="flex w-[320px] shrink-0 flex-col border-r border-black/10 bg-white">
                    {selectedSection && selectedSchema ? (
                        <FieldPanel
                            section={selectedSection}
                            schema={selectedSchema}
                            onChange={changeField}
                            onBack={deselect}
                        />
                    ) : (
                        <SectionTree
                            sections={doc.sections}
                            schemas={schemaQuery.data ?? []}
                            selectedId={selectedId}
                            onSelect={selectSection}
                            onAdd={addSection}
                            onRemove={removeSection}
                            onToggleVisible={toggleVisible}
                            onReorder={reorder}
                        />
                    )}
                </aside>

                <PreviewFrame
                    src={previewUrl}
                    device={device}
                    iframeRef={iframeRef}
                    onMessage={onPreviewMessage}
                />
            </div>
        </div>
    );
}
