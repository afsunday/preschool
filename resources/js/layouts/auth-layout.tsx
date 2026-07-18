import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

type AuthLayoutProps = {
    title?: string;
    description?: string;
    eyebrow?: string;
    /** Optional top-of-form cross-link, e.g. "Already a member? · Sign in". */
    altPrompt?: string;
    altLabel?: string;
    altHref?: string;
    children: ReactNode;
};

export default function AuthLayout({
    title = '',
    description = '',
    eyebrow,
    altPrompt,
    altLabel,
    altHref,
    children,
}: AuthLayoutProps) {
    return (
        <div className="flex min-h-svh flex-col bg-white lg:flex-row lg:gap-0 lg:p-3">
            {/* Brand panel — a full side on desktop, a compact header band on mobile. */}
            <aside className="relative isolate overflow-hidden bg-gradient-to-br from-[#4b4ff0] via-[#6a4bf2] to-[#8a4bf5] px-6 pt-9 pb-11 text-white lg:flex lg:w-[43%] lg:flex-col lg:rounded-[28px] lg:px-10 lg:pt-10 lg:pb-8">
                {/* Mobile: little snapshots flanking the centred logo. */}
                <MobileSnaps />

                <DotGrid className="pointer-events-none absolute -top-6 right-4 hidden h-44 w-44 text-white/20 lg:block" />

                <div className="relative z-10 flex items-center justify-center lg:justify-start">
                    <WodiMark />
                </div>

                <h2 className="relative mt-10 hidden max-w-[16ch] text-[2.6rem] leading-[1.08] font-extrabold tracking-tight lg:block">
                    Every day at daycare, followed from home.
                </h2>
                <p className="relative mt-4 hidden max-w-[34ch] text-white/70 lg:block">
                    Photos, daily reports and messages from your child’s room —
                    all in one place.
                </p>

                {/* Desktop only: the big illustration anchors the tall panel. */}
                <div className="relative mt-auto hidden items-end lg:flex">
                    <DaycareScene className="h-64 w-auto" />
                </div>
            </aside>

            {/* Form column. */}
            <main className="flex flex-1 flex-col px-6 py-8 sm:px-10 lg:px-16 lg:py-10">
                {altHref && (
                    <div className="text-center text-sm text-neutral-500 lg:text-right">
                        {altPrompt}{' '}
                        <Link
                            href={altHref}
                            className="font-semibold text-[#6c4cf1] hover:underline"
                        >
                            {altLabel}
                        </Link>
                    </div>
                )}

                <div className="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-6">
                    {eyebrow && (
                        <p className="text-xs font-semibold tracking-[0.22em] text-neutral-400 uppercase">
                            {eyebrow}
                        </p>
                    )}
                    <h1 className="mt-2 text-3xl font-extrabold tracking-tight text-neutral-900">
                        {title}
                    </h1>
                    {description && (
                        <p className="mt-2 text-sm text-neutral-500">
                            {description}
                        </p>
                    )}

                    <div className="mt-8">{children}</div>
                </div>

                <footer className="mx-auto w-full max-w-md pt-6 text-center text-xs text-neutral-400">
                    © {new Date().getFullYear()} WODI Daycare · Made with care
                </footer>
            </main>
        </div>
    );
}

/** WODI wordmark + a little smiling badge (all SVG, no image assets). */
function WodiMark() {
    return (
        <span className="inline-flex items-center gap-2.5">
            <span className="grid size-10 place-items-center rounded-2xl bg-white shadow-sm">
                <svg viewBox="0 0 32 32" className="size-6" aria-hidden="true">
                    <circle cx="16" cy="16" r="13" fill="#ffcc00" />
                    <circle cx="11.5" cy="14" r="1.9" fill="#3b2f6b" />
                    <circle cx="20.5" cy="14" r="1.9" fill="#3b2f6b" />
                    <path
                        d="M11 19.5c1.6 2.4 8.4 2.4 10 0"
                        fill="none"
                        stroke="#3b2f6b"
                        strokeWidth="2"
                        strokeLinecap="round"
                    />
                </svg>
            </span>
            <span className="text-2xl font-extrabold tracking-tight">WODI</span>
        </span>
    );
}

/** Decorative dot grid. */
function DotGrid({ className }: { className?: string }) {
    return (
        <svg className={className} viewBox="0 0 100 100" aria-hidden="true">
            <defs>
                <pattern
                    id="auth-dots"
                    width="14"
                    height="14"
                    patternUnits="userSpaceOnUse"
                >
                    <circle cx="2" cy="2" r="2" fill="currentColor" />
                </pattern>
            </defs>
            <rect width="100" height="100" fill="url(#auth-dots)" />
        </svg>
    );
}

const STAR =
    'M0,-11 L3.2,-3.4 L11.4,-3.4 L4.9,1.3 L7.1,9 L0,4.4 L-7.1,9 L-4.9,1.3 L-11.4,-3.4 L-3.2,-3.4 Z';

/** A cheerful daycare scene — sun, cloud, stars, blocks. Entirely SVG. */
function DaycareScene({ className }: { className?: string }) {
    return (
        <svg
            className={className}
            viewBox="0 0 260 240"
            fill="none"
            aria-hidden="true"
        >
            {/* scattered stars */}
            <path
                d={STAR}
                transform="translate(34 42) scale(1.1)"
                fill="#ffd84d"
            />
            <path
                d={STAR}
                transform="translate(232 66) scale(0.8)"
                fill="#ffd84d"
            />
            <path
                d={STAR}
                transform="translate(214 158) scale(0.9)"
                fill="#ffe27a"
            />
            <circle cx="20" cy="120" r="5" fill="#ffffff" opacity="0.5" />
            <circle cx="244" cy="112" r="4" fill="#ffffff" opacity="0.5" />
            <circle cx="60" cy="30" r="3.5" fill="#ffffff" opacity="0.6" />

            {/* sun */}
            <g transform="translate(168 96)">
                {Array.from({ length: 12 }).map((_, i) => (
                    <rect
                        key={i}
                        x="-2.5"
                        y="-64"
                        width="5"
                        height="14"
                        rx="2.5"
                        fill="#ffd84d"
                        transform={`rotate(${i * 30})`}
                    />
                ))}
                <circle r="46" fill="#ffcc00" />
                <circle r="46" fill="#ffffff" opacity="0.12" />
                <circle cx="-15" cy="-6" r="4.5" fill="#5a3f13" />
                <circle cx="15" cy="-6" r="4.5" fill="#5a3f13" />
                <circle cx="-22" cy="7" r="6" fill="#ff9a3d" opacity="0.55" />
                <circle cx="22" cy="7" r="6" fill="#ff9a3d" opacity="0.55" />
                <path
                    d="M-16 10c6 9 26 9 32 0"
                    fill="none"
                    stroke="#5a3f13"
                    strokeWidth="4"
                    strokeLinecap="round"
                />
            </g>

            {/* cloud */}
            <g transform="translate(52 158)">
                <ellipse cx="0" cy="14" rx="30" ry="18" fill="#ffffff" />
                <circle cx="-20" cy="6" r="16" fill="#ffffff" />
                <circle cx="4" cy="-4" r="21" fill="#ffffff" />
                <circle cx="26" cy="6" r="15" fill="#ffffff" />
                <rect
                    x="-30"
                    y="16"
                    width="72"
                    height="18"
                    rx="9"
                    fill="#ffffff"
                />
            </g>

            {/* stacking blocks */}
            <g transform="translate(150 196)">
                <rect
                    x="0"
                    y="0"
                    width="34"
                    height="34"
                    rx="8"
                    fill="#16c79a"
                />
                <rect
                    x="34"
                    y="0"
                    width="34"
                    height="34"
                    rx="8"
                    fill="#ff8a3d"
                />
                <rect
                    x="17"
                    y="-32"
                    width="34"
                    height="34"
                    rx="8"
                    fill="#ec1e79"
                />
                <circle cx="17" cy="17" r="5.5" fill="#ffffff" opacity="0.85" />
                <circle cx="51" cy="17" r="5.5" fill="#ffffff" opacity="0.85" />
                <circle
                    cx="34"
                    cy="-15"
                    r="5.5"
                    fill="#ffffff"
                    opacity="0.85"
                />
            </g>
        </svg>
    );
}

/** The four little snapshots that flank the logo on the mobile header band. */
function MobileSnaps() {
    return (
        <div
            className="pointer-events-none absolute inset-0 z-0 lg:hidden"
            aria-hidden="true"
        >
            <SnapCard
                tone="#a9dcff"
                className="absolute top-4 -left-4 w-20 -rotate-[14deg]"
                scene={<SunGlyph />}
            />
            <SnapCard
                tone="#ffcfe1"
                className="absolute top-9 left-11 w-16 rotate-[8deg]"
                scene={<FaceGlyph />}
            />
            <SnapCard
                tone="#bff0d6"
                className="absolute top-4 -right-4 w-20 rotate-[14deg]"
                scene={<StarGlyph />}
            />
            <SnapCard
                tone="#d8ccff"
                className="absolute top-9 right-11 w-16 -rotate-[8deg]"
                scene={<CloudGlyph />}
            />
        </div>
    );
}

/** A tilted little "photo" — a polaroid frame with a mini scene. All SVG. */
function SnapCard({
    tone,
    scene,
    className,
}: {
    tone: string;
    scene: ReactNode;
    className?: string;
}) {
    return (
        <svg
            viewBox="0 0 80 94"
            className={`${className} drop-shadow-md`}
            aria-hidden="true"
        >
            <rect x="3" y="3" width="74" height="88" rx="7" fill="#ffffff" />
            <rect x="9" y="9" width="62" height="60" rx="4" fill={tone} />
            {scene}
        </svg>
    );
}

function SunGlyph() {
    return (
        <g transform="translate(40 37)">
            {Array.from({ length: 8 }).map((_, i) => (
                <rect
                    key={i}
                    x="-1.6"
                    y="-20"
                    width="3.2"
                    height="6"
                    rx="1.6"
                    fill="#ffb800"
                    transform={`rotate(${i * 45})`}
                />
            ))}
            <circle r="12" fill="#ffcc00" />
            <circle cx="-4" cy="-1" r="1.6" fill="#5a3f13" />
            <circle cx="4" cy="-1" r="1.6" fill="#5a3f13" />
            <path
                d="M-5 3c2.5 3.5 7.5 3.5 10 0"
                fill="none"
                stroke="#5a3f13"
                strokeWidth="1.6"
                strokeLinecap="round"
            />
        </g>
    );
}

function FaceGlyph() {
    return (
        <g transform="translate(40 37)">
            <circle r="13" fill="#ffffff" />
            <circle cx="-4.5" cy="-2" r="1.8" fill="#3b2f6b" />
            <circle cx="4.5" cy="-2" r="1.8" fill="#3b2f6b" />
            <circle cx="-7" cy="3" r="2.4" fill="#ffb3cc" />
            <circle cx="7" cy="3" r="2.4" fill="#ffb3cc" />
            <path
                d="M-5 3.5c2.5 3 7.5 3 10 0"
                fill="none"
                stroke="#3b2f6b"
                strokeWidth="1.8"
                strokeLinecap="round"
            />
        </g>
    );
}

function StarGlyph() {
    return (
        <path d={STAR} transform="translate(40 39) scale(1.3)" fill="#ffd84d" />
    );
}

function CloudGlyph() {
    return (
        <g transform="translate(40 40)">
            <circle cx="-10" cy="2" r="8" fill="#ffffff" />
            <circle cx="2" cy="-4" r="10" fill="#ffffff" />
            <circle cx="12" cy="2" r="7" fill="#ffffff" />
            <rect x="-18" y="2" width="36" height="9" rx="4.5" fill="#ffffff" />
        </g>
    );
}
