import { Loader2 } from 'lucide-react';
import type { RefObject } from 'react';
import { useEffect, useRef, useState } from 'react';

export type Device = 'desktop' | 'tablet' | 'mobile';

// True viewport widths so the site's own breakpoints fire. Desktop is wide
// enough to trip `lg:` (1024px); the frame is then scaled to fit the stage.
export const DEVICE_WIDTH: Record<Device, number> = {
    desktop: 1440,
    tablet: 820,
    mobile: 390,
};

export interface PreviewMessage {
    source: 'cms-preview';
    type: 'ready' | 'select';
    id?: number;
}

export function PreviewFrame({
    html,
    device,
    loading,
    iframeRef,
    onMessage,
}: {
    html: string | null;
    device: Device;
    loading: boolean;
    iframeRef: RefObject<HTMLIFrameElement | null>;
    onMessage: (msg: PreviewMessage) => void;
}) {
    const stageRef = useRef<HTMLDivElement>(null);
    const [stage, setStage] = useState({ w: 0, h: 0 });

    useEffect(() => {
        const el = stageRef.current;

        if (!el) {
            return;
        }

        const ro = new ResizeObserver(() =>
            setStage({ w: el.clientWidth, h: el.clientHeight }),
        );
        ro.observe(el);

        return () => ro.disconnect();
    }, []);

    useEffect(() => {
        const handler = (e: MessageEvent) => {
            if (e.data?.source === 'cms-preview') {
                onMessage(e.data);
            }
        };
        window.addEventListener('message', handler);

        return () => window.removeEventListener('message', handler);
    }, [onMessage]);

    const deviceW = DEVICE_WIDTH[device];
    // No wrapper padding — the iframe fills top to bottom.
    const scale = Math.min(1, stage.w / deviceW);
    const frameH = scale > 0 ? stage.h / scale : stage.h;

    return (
        <div
            ref={stageRef}
            className="relative flex flex-1 justify-center overflow-hidden bg-neutral-200/60"
            style={{
                backgroundImage:
                    'radial-gradient(circle, rgba(0,0,0,0.06) 1px, transparent 1px)',
                backgroundSize: '16px 16px',
            }}
        >
            {loading && (
                <div className="pointer-events-none absolute top-3 right-3 z-10 flex items-center gap-1.5 rounded-full bg-white/90 px-2.5 py-1 text-xs text-neutral-500 shadow-sm">
                    <Loader2 className="size-3.5 animate-spin" /> rendering
                </div>
            )}

            {stage.w > 0 && html !== null && (
                <div
                    style={{ width: deviceW * scale, height: stage.h }}
                    className="shrink-0 bg-white shadow-[0_0_0_1px_rgba(0,0,0,0.06)]"
                >
                    <iframe
                        ref={iframeRef}
                        srcDoc={html}
                        title="Page preview"
                        style={{
                            width: deviceW,
                            height: frameH,
                            transform: `scale(${scale})`,
                            transformOrigin: 'top left',
                        }}
                        className="border-0 bg-white"
                    />
                </div>
            )}
        </div>
    );
}
