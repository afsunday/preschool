import Image from '@tiptap/extension-image';
import {
    NodeViewWrapper,
    ReactNodeViewRenderer
    
} from '@tiptap/react';
import type {NodeViewProps} from '@tiptap/react';
import { AlignCenter, AlignLeft, AlignRight } from 'lucide-react';
import { useRef, useState } from 'react';
import { cn } from '@/lib/utils';

type Align = 'left' | 'center' | 'right';

/**
 * The picked image, with drag-to-resize, size presets and left/center/right
 * alignment (like SunEditor). The width (HTML `width` attr) and alignment
 * (margin style) are baked into the stored HTML, so they survive into the sent
 * email — where `.nl-body img { max-width:100% }` keeps it responsive.
 */
function ResizableImageView({
    node,
    updateAttributes,
    selected,
}: NodeViewProps) {
    const imgRef = useRef<HTMLImageElement>(null);
    const [dragging, setDragging] = useState(false);
    const [liveWidth, setLiveWidth] = useState<number | null>(null);

    const width = node.attrs.width as number | null;
    const align = (node.attrs.align as Align) ?? 'center';
    const activeUi = selected || dragging;

    const startResize = (event: React.MouseEvent) => {
        event.preventDefault();
        event.stopPropagation();
        setDragging(true);

        const startX = event.clientX;
        const startWidth = imgRef.current?.offsetWidth ?? 0;

        const onMove = (e: MouseEvent) => {
            const next = Math.max(
                60,
                Math.round(startWidth + (e.clientX - startX)),
            );
            setLiveWidth(next);
            updateAttributes({ width: next });
        };

        const onUp = () => {
            setDragging(false);
            window.removeEventListener('mousemove', onMove);
            window.removeEventListener('mouseup', onUp);
        };

        window.addEventListener('mousemove', onMove);
        window.addEventListener('mouseup', onUp);
    };

    // Clicks on the controls must not clear the node selection.
    const hold = (fn: () => void) => (e: React.MouseEvent) => {
        e.preventDefault();
        e.stopPropagation();
        fn();
    };

    return (
        <NodeViewWrapper
            data-drag-handle
            className={cn(
                'group flex leading-none',
                align === 'center'
                    ? 'justify-center'
                    : align === 'right'
                      ? 'justify-end'
                      : 'justify-start',
            )}
        >
            <div className="relative inline-block">
                <img
                    ref={imgRef}
                    src={node.attrs.src as string}
                    alt={(node.attrs.alt as string) ?? ''}
                    draggable={false}
                    style={{
                        width: width ? `${width}px` : undefined,
                        maxWidth: '100%',
                        // Default cap so a big pick isn't huge until sized.
                        maxHeight: width ? undefined : '200px',
                        height: 'auto',
                    }}
                    className={cn(
                        'block rounded transition',
                        activeUi
                            ? 'ring-wodi-pink ring-2'
                            : 'group-hover:ring-wodi-pink/40 ring-2 ring-transparent',
                    )}
                />

                {/* Controls — overlaid on the image so they never get clipped. */}
                <div
                    contentEditable={false}
                    className={cn(
                        'absolute top-1.5 left-1.5 flex items-center gap-0.5 rounded-md bg-neutral-900/85 p-0.5 text-[11px] font-medium text-white shadow-lg transition',
                        activeUi
                            ? 'opacity-100'
                            : 'pointer-events-none opacity-0 group-hover:opacity-100',
                    )}
                >
                    <button
                        type="button"
                        onMouseDown={hold(() =>
                            updateAttributes({ width: 220 }),
                        )}
                        className="rounded px-1.5 py-0.5 hover:bg-white/20"
                    >
                        S
                    </button>
                    <button
                        type="button"
                        onMouseDown={hold(() =>
                            updateAttributes({ width: 420 }),
                        )}
                        className="rounded px-1.5 py-0.5 hover:bg-white/20"
                    >
                        M
                    </button>
                    <button
                        type="button"
                        onMouseDown={hold(() =>
                            updateAttributes({ width: null }),
                        )}
                        className="rounded px-1.5 py-0.5 hover:bg-white/20"
                    >
                        Full
                    </button>

                    <span className="mx-0.5 h-4 w-px bg-white/25" />

                    {(
                        [
                            ['left', AlignLeft],
                            ['center', AlignCenter],
                            ['right', AlignRight],
                        ] as [Align, typeof AlignLeft][]
                    ).map(([value, Icon]) => (
                        <button
                            key={value}
                            type="button"
                            onMouseDown={hold(() =>
                                updateAttributes({ align: value }),
                            )}
                            className={cn(
                                'grid size-6 place-items-center rounded hover:bg-white/20',
                                align === value && 'bg-white/25',
                            )}
                        >
                            <Icon className="size-3.5" />
                        </button>
                    ))}
                </div>

                {/* Drag-to-resize corner grip. */}
                <span
                    onMouseDown={startResize}
                    title="Drag to resize"
                    contentEditable={false}
                    className={cn(
                        'bg-wodi-pink absolute -right-2 -bottom-2 grid size-5 cursor-nwse-resize place-items-center rounded-full border-2 border-white shadow-md transition',
                        activeUi
                            ? 'opacity-100'
                            : 'pointer-events-none opacity-0 group-hover:opacity-100',
                    )}
                >
                    <span className="size-1.5 rounded-full bg-white/90" />
                </span>

                {dragging && liveWidth && (
                    <span
                        contentEditable={false}
                        className="absolute right-2 bottom-2 rounded bg-black/75 px-1.5 py-0.5 text-[10px] font-medium text-white"
                    >
                        {liveWidth}px
                    </span>
                )}
            </div>
        </NodeViewWrapper>
    );
}

export const ResizableImage = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            width: {
                default: null,
                parseHTML: (el) => {
                    const w = el.getAttribute('width');

                    return w ? parseInt(w, 10) : null;
                },
                renderHTML: (attrs) =>
                    attrs.width ? { width: attrs.width } : {},
            },
            align: {
                default: 'center',
                parseHTML: (el) => el.getAttribute('data-align') ?? 'center',
                renderHTML: (attrs) => {
                    const margin =
                        attrs.align === 'left'
                            ? 'margin-right:auto;'
                            : attrs.align === 'right'
                              ? 'margin-left:auto;'
                              : 'margin-left:auto;margin-right:auto;';

                    return {
                        'data-align': attrs.align,
                        style: `display:block;max-width:100%;height:auto;${margin}`,
                    };
                },
            },
        };
    },

    addNodeView() {
        return ReactNodeViewRenderer(ResizableImageView);
    },
});
