// A warm, playful palette so avatars aren't all one colour — the ClassDojo look.
// Each name maps to a stable colour. The classes are literal strings so Tailwind
// keeps them in the build.
const AVATAR_COLORS = [
    'bg-rose-100 text-rose-600',
    'bg-orange-100 text-orange-600',
    'bg-amber-100 text-amber-700',
    'bg-emerald-100 text-emerald-600',
    'bg-teal-100 text-teal-600',
    'bg-sky-100 text-sky-600',
    'bg-indigo-100 text-indigo-600',
    'bg-violet-100 text-violet-600',
    'bg-fuchsia-100 text-fuchsia-600',
    'bg-pink-100 text-pink-600',
];

/** A consistent bg + text colour pair for a person, derived from their name. */
export function avatarColor(name: string): string {
    let hash = 0;

    for (let i = 0; i < name.length; i += 1) {
        hash = (hash * 31 + name.charCodeAt(i)) | 0;
    }

    return AVATAR_COLORS[Math.abs(hash) % AVATAR_COLORS.length];
}
