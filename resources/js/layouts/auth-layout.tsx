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
        <div className="flex min-h-svh flex-col bg-white lg:flex-row lg:p-3">
            {/* Brand panel — a full side on desktop, a compact header band on
                mobile. On mobile it sits above the form (z-10) and lets the
                snapshots spill past its bottom edge (overflow-visible). */}
            <aside className="relative isolate z-10 overflow-visible [border-bottom-right-radius:10%_28px] [border-bottom-left-radius:10%_28px] bg-gradient-to-br from-[#ff5fa2] via-[#ec1e79] to-[#b3134f] px-6 pt-6 pb-6 text-white lg:z-auto lg:flex lg:w-[43%] lg:flex-col lg:overflow-hidden lg:rounded-[28px] lg:[border-bottom-right-radius:28px] lg:[border-bottom-left-radius:28px] lg:px-10 lg:pt-10 lg:pb-8">
                {/* Mobile: little snapshots straddling the band's bottom edge. */}
                <MobileSnaps />

                <DotGrid className="pointer-events-none absolute -top-6 right-4 hidden h-44 w-44 text-white/20 lg:block" />

                <div className="relative z-10 flex items-center justify-center lg:justify-start">
                    <WodiMark />
                </div>

                <h2 className="relative mt-10 hidden max-w-[16ch] text-[2.6rem] leading-[1.08] font-extrabold tracking-tight lg:block">
                    Every day at childcare, followed from home.
                </h2>
                <p className="relative mt-4 hidden max-w-[34ch] text-white/75 lg:block">
                    Photos, daily reports and messages from your child’s room —
                    all in one place.
                </p>

                {/* Desktop only: the big illustration anchors the tall panel. */}
                <div className="relative mt-auto hidden items-end justify-center lg:flex">
                    <DaycareScene className="h-72 w-auto" />
                </div>
            </aside>

            {/* Form column. Extra top room on mobile so the spilling snapshots
                land on empty white, not on the content. */}
            <main className="flex flex-1 flex-col px-6 pt-11 pb-8 sm:px-10 lg:px-16 lg:py-10">
                {altHref && (
                    <div className="text-center text-sm text-neutral-500 lg:text-right">
                        {altPrompt}{' '}
                        <Link
                            href={altHref}
                            className="font-semibold text-[#ec1e79] hover:underline"
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
                    © {new Date().getFullYear()} WODI Childcare · Made with care
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

const CREST = [
    { deg: -44, fill: '#ff5a5f' },
    { deg: -22, fill: '#ff8a3d' },
    { deg: 0, fill: '#ffcc00' },
    { deg: 22, fill: '#16c79a' },
    { deg: 44, fill: '#0d99ff' },
];

/** WODI's daycare mascot — a cheery cub with a rainbow crest and balloons. */
function DaycareScene({ className }: { className?: string }) {
    return (
        <svg
            className={className}
            viewBox="0 0 280 276"
            fill="none"
            aria-hidden="true"
        >
            <defs>
                <linearGradient id="cub" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stopColor="#ffffff" />
                    <stop offset="1" stopColor="#ffe3ef" />
                </linearGradient>
                <radialGradient id="belly" cx="0.5" cy="0.4" r="0.62">
                    <stop offset="0" stopColor="#ffffff" />
                    <stop offset="1" stopColor="#fff0f6" />
                </radialGradient>
            </defs>

            {/* confetti + sparkles */}
            <path
                d={STAR}
                transform="translate(40 90) scale(0.9)"
                fill="#ffd21e"
            />
            <path
                d={STAR}
                transform="translate(250 150) scale(0.72)"
                fill="#ffd21e"
            />
            <circle cx="24" cy="140" r="5" fill="#16c79a" />
            <circle cx="256" cy="112" r="5" fill="#ffd21e" />
            <circle cx="34" cy="196" r="4" fill="#0d99ff" />
            <circle cx="258" cy="196" r="4.5" fill="#ff5a9e" />
            <circle cx="20" cy="98" r="3.5" fill="#6c5ce7" />
            <circle cx="264" cy="70" r="3.5" fill="#ff8a3d" />
            <rect
                x="44"
                y="58"
                width="9"
                height="9"
                rx="2"
                fill="#6c5ce7"
                transform="rotate(22 48 62)"
            />
            <rect
                x="234"
                y="206"
                width="9"
                height="9"
                rx="2"
                fill="#16c79a"
                transform="rotate(-16 238 210)"
            />

            {/* sun */}
            <g transform="translate(236 56)">
                {Array.from({ length: 8 }).map((_, i) => (
                    <rect
                        key={i}
                        x="-2"
                        y="-30"
                        width="4"
                        height="9"
                        rx="2"
                        fill="#ffb300"
                        transform={`rotate(${i * 45})`}
                    />
                ))}
                <circle r="18" fill="#ffd21e" />
                <circle cx="-6" cy="-2" r="2.2" fill="#7a4a10" />
                <circle cx="6" cy="-2" r="2.2" fill="#7a4a10" />
                <path
                    d="M-7 4c3 4 11 4 14 0"
                    fill="none"
                    stroke="#7a4a10"
                    strokeWidth="2.4"
                    strokeLinecap="round"
                />
            </g>

            {/* balloon strings (behind balloons), gathered at the raised paw */}
            <g
                fill="none"
                stroke="#ffffff"
                strokeWidth="1.3"
                strokeLinecap="round"
                opacity="0.65"
            >
                <path d="M58 88C70 110 90 126 104 140" />
                <path d="M90 76C93 106 98 126 104 140" />
                <path d="M34 112C56 124 90 133 104 140" />
                <path d="M74 111C84 124 98 133 104 140" />
            </g>

            {/* balloons */}
            <Balloon cx={58} cy={66} rx={17} ry={20} fill="#16c79a" />
            <Balloon cx={90} cy={54} rx={17} ry={20} fill="#ffcc00" />
            <Balloon cx={34} cy={92} rx={15} ry={18} fill="#0d99ff" />
            <Balloon cx={74} cy={91} rx={14} ry={17} fill="#ff5a9e" />

            {/* --- the cub --- */}
            {/* ground shadow */}
            <ellipse
                cx="152"
                cy="256"
                rx="58"
                ry="11"
                fill="#7a0d3c"
                opacity="0.18"
            />

            {/* rainbow crest */}
            <g transform="translate(152 118)">
                {CREST.map((p) => (
                    <path
                        key={p.deg}
                        d="M0 2C-5 -5 -5 -20 0 -27C5 -20 5 -5 0 2Z"
                        fill={p.fill}
                        transform={`rotate(${p.deg})`}
                    />
                ))}
            </g>

            {/* ears */}
            <circle cx="122" cy="122" r="20" fill="url(#cub)" />
            <circle cx="182" cy="122" r="20" fill="url(#cub)" />
            <circle cx="122" cy="124" r="9.5" fill="#ffb3d1" />
            <circle cx="182" cy="124" r="9.5" fill="#ffb3d1" />

            {/* arms */}
            <ellipse
                cx="104"
                cy="150"
                rx="15"
                ry="19"
                fill="url(#cub)"
                transform="rotate(-32 104 150)"
            />
            <ellipse
                cx="200"
                cy="194"
                rx="15"
                ry="20"
                fill="url(#cub)"
                transform="rotate(24 200 194)"
            />

            {/* feet */}
            <ellipse cx="128" cy="252" rx="18" ry="12" fill="url(#cub)" />
            <ellipse cx="176" cy="252" rx="18" ry="12" fill="url(#cub)" />
            <ellipse cx="128" cy="254" rx="8.5" ry="5" fill="#ffb3d1" />
            <ellipse cx="176" cy="254" rx="8.5" ry="5" fill="#ffb3d1" />

            {/* body */}
            <rect
                x="94"
                y="118"
                width="116"
                height="140"
                rx="58"
                fill="url(#cub)"
            />
            <ellipse
                cx="132"
                cy="148"
                rx="26"
                ry="15"
                fill="#ffffff"
                opacity="0.5"
            />
            <ellipse cx="152" cy="198" rx="40" ry="46" fill="url(#belly)" />

            {/* face */}
            <ellipse cx="132" cy="170" rx="8.5" ry="11" fill="#4a3350" />
            <ellipse cx="172" cy="170" rx="8.5" ry="11" fill="#4a3350" />
            <circle cx="135" cy="165" r="3" fill="#ffffff" />
            <circle cx="175" cy="165" r="3" fill="#ffffff" />
            <circle cx="129" cy="174" r="1.4" fill="#ffffff" opacity="0.8" />
            <circle cx="169" cy="174" r="1.4" fill="#ffffff" opacity="0.8" />
            <circle cx="115" cy="186" r="10" fill="#ff77ac" opacity="0.7" />
            <circle cx="189" cy="186" r="10" fill="#ff77ac" opacity="0.7" />
            <ellipse cx="152" cy="182" rx="4" ry="3" fill="#e06a94" />
            <path
                d="M152 185v4"
                stroke="#c85c86"
                strokeWidth="2.2"
                strokeLinecap="round"
            />
            <path d="M137 189c8 10 22 10 30 0" fill="#6b3a56" />
            <path d="M144 195c5 5 11 5 16 0Z" fill="#ff87ac" />
        </svg>
    );
}

function Balloon({
    cx,
    cy,
    rx,
    ry,
    fill,
}: {
    cx: number;
    cy: number;
    rx: number;
    ry: number;
    fill: string;
}) {
    return (
        <g>
            <ellipse cx={cx} cy={cy} rx={rx} ry={ry} fill={fill} />
            <ellipse
                cx={cx - rx * 0.35}
                cy={cy - ry * 0.35}
                rx={rx * 0.28}
                ry={ry * 0.34}
                fill="#ffffff"
                opacity="0.4"
            />
            <path
                d={`M${cx - 3} ${cy + ry} L${cx + 3} ${cy + ry} L${cx} ${cy + ry + 5} Z`}
                fill={fill}
            />
        </g>
    );
}

/** Four little snapshots that straddle the mobile band's bottom edge. */
function MobileSnaps() {
    return (
        <div
            className="pointer-events-none absolute inset-0 z-0 lg:hidden"
            aria-hidden="true"
        >
            <SnapCard
                tone="#8fd0ff"
                className="absolute -bottom-6 left-1 w-[68px] -rotate-[12deg]"
                scene={<SunGlyph />}
            />
            <SnapCard
                tone="#f3e6ff"
                className="absolute -bottom-2 left-[60px] w-[52px] rotate-[9deg]"
                scene={<RainbowGlyph />}
            />
            <SnapCard
                tone="#b8f0cf"
                className="absolute right-1 -bottom-6 w-[68px] rotate-[12deg]"
                scene={<StarGlyph />}
            />
            <SnapCard
                tone="#ffd0e2"
                className="absolute right-[60px] -bottom-2 w-[52px] -rotate-[9deg]"
                scene={<FaceGlyph />}
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
                    fill="#ffb300"
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

function RainbowGlyph() {
    const bands = ['#ff5a5f', '#ff8a3d', '#ffcc00', '#16c79a', '#0d99ff'];

    return (
        <g
            transform="translate(40 46)"
            fill="none"
            strokeWidth="3.4"
            strokeLinecap="round"
        >
            {bands.map((c, i) => (
                <path
                    key={c}
                    d={`M${-16 + i * 3.4} 6A${16 - i * 3.4} ${16 - i * 3.4} 0 0 1 ${16 - i * 3.4} 6`}
                    stroke={c}
                />
            ))}
            <circle cx="-16" cy="9" r="3" fill="#ffffff" stroke="none" />
            <circle cx="16" cy="9" r="3" fill="#ffffff" stroke="none" />
        </g>
    );
}

function StarGlyph() {
    return (
        <path
            d={STAR}
            transform="translate(40 39) scale(1.35)"
            fill="#ffcc00"
        />
    );
}

function FaceGlyph() {
    return (
        <g transform="translate(40 37)">
            <circle r="13" fill="#ffffff" />
            <circle cx="-4.5" cy="-2" r="1.9" fill="#3b2f6b" />
            <circle cx="4.5" cy="-2" r="1.9" fill="#3b2f6b" />
            <circle cx="-7" cy="3" r="2.6" fill="#ff9ec4" />
            <circle cx="7" cy="3" r="2.6" fill="#ff9ec4" />
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
