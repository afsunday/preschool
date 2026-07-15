import { RefObject, useEffect, useRef, useState } from 'react';

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
    src,
    device,
    iframeRef,
    onMessage,
}: {
    src: string;
    device: Device;
    iframeRef: RefObject<HTMLIFrameElement | null>;
    onMessage: (msg: PreviewMessage) => void;
}) {
    const stageRef = useRef<HTMLDivElement>(null);
    const [stage, setStage] = useState({ w: 0, h: 0 });

    useEffect(() => {
        const el = stageRef.current;
        if (!el) return;
        const ro = new ResizeObserver(() =>
            setStage({ w: el.clientWidth, h: el.clientHeight }),
        );
        ro.observe(el);
        return () => ro.disconnect();
    }, []);

    useEffect(() => {
        const handler = (e: MessageEvent) => {
            if (e.data?.source === 'cms-preview') onMessage(e.data);
        };
        window.addEventListener('message', handler);
        return () => window.removeEventListener('message', handler);
    }, [onMessage]);

    const deviceW = DEVICE_WIDTH[device];
    const pad = device === 'desktop' ? 24 : 48;
    const scale = Math.min(1, (stage.w - pad) / deviceW);
    const frameH = scale > 0 ? (stage.h - pad) / scale : stage.h;

    return (
        <div
            ref={stageRef}
            className="flex flex-1 justify-center overflow-hidden bg-neutral-200/60 p-3"
            style={{
                backgroundImage:
                    'radial-gradient(circle, rgba(0,0,0,0.06) 1px, transparent 1px)',
                backgroundSize: '16px 16px',
            }}
        >
            {stage.w > 0 && (
                <div
                    style={{ width: deviceW * scale, height: stage.h - pad }}
                    className="shrink-0"
                >
                    <iframe
                        ref={iframeRef}
                        src={src}
                        title="Page preview"
                        style={{
                            width: deviceW,
                            height: frameH,
                            transform: `scale(${scale})`,
                            transformOrigin: 'top left',
                        }}
                        className="rounded-[6px] border-0 bg-white shadow-lg ring-1 ring-black/10"
                    />
                </div>
            )}
        </div>
    );
}
