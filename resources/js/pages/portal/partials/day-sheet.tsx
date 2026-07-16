import { Description, Field, Label, Switch } from '@headlessui/react';
import { router } from '@inertiajs/react';
import {
    Baby,
    Moon,
    Send,
    Smile,
    StickyNote,
    Trash2,
    Utensils,
    X,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import SideModal from '@/components/side-modal';
import { PhotoUpload } from './photo-upload';
import { cn } from '@/lib/utils';
import type {
    PortalReportChild,
    PortalReportEntry,
    ReportEntryType,
    ReportOptions,
} from '@/types/portal';

/** The five things a teacher logs. Order is the order of the composer. */
const KINDS: { type: ReportEntryType; label: string; icon: LucideIcon }[] = [
    { type: 'mood', label: 'Mood', icon: Smile },
    { type: 'nap', label: 'Nap', icon: Moon },
    { type: 'meal', label: 'Meal', icon: Utensils },
    { type: 'nappy', label: 'Nappy', icon: Baby },
    { type: 'note', label: 'Note', icon: StickyNote },
];

const ICONS: Record<string, LucideIcon> = {
    mood: Smile,
    nap: Moon,
    meal: Utensils,
    nappy: Baby,
    note: StickyNote,
    photo: StickyNote,
};

type Meridiem = 'AM' | 'PM';

/** "2" + "05" + PM -> "14:05". Empty or nonsense -> '' (meaning "now"). */
function to24(hour: string, minute: string, meridiem: Meridiem): string {
    const h = Number.parseInt(hour, 10);
    const m = minute === '' ? 0 : Number.parseInt(minute, 10);

    if (
        !Number.isFinite(h) ||
        !Number.isFinite(m) ||
        h < 1 ||
        h > 12 ||
        m > 59
    ) {
        return '';
    }

    const h24 = meridiem === 'PM' ? (h % 12) + 12 : h % 12;

    return `${String(h24).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
}

/** The field holds 24h "HH:mm"; the API wants a datetime on the report's day. */
function onDate(date: string, time: string): string | null {
    return time ? `${date} ${time}` : null;
}

/**
 * Type the time, toggle AM/PM. The native `type="time"` picker looks different
 * in every browser and ignores the rest of this design.
 */
function TimeField({
    label,
    onChange,
}: {
    label: string;
    onChange: (value: string) => void;
}) {
    const [hour, setHour] = useState('');
    const [minute, setMinute] = useState('');
    const [meridiem, setMeridiem] = useState<Meridiem>('PM');

    const push = (h: string, m: string, mer: Meridiem) => {
        setHour(h);
        setMinute(m);
        setMeridiem(mer);
        onChange(to24(h, m, mer));
    };

    // Digits only, and never more than the field can mean.
    const clean = (raw: string, max: number) => {
        const digits = raw.replace(/\D/g, '').slice(0, 2);

        return digits === ''
            ? ''
            : String(Math.min(Number.parseInt(digits, 10), max));
    };

    const box =
        'w-10 rounded-[4px] border border-portal-line bg-white px-1.5 py-1.5 text-center text-sm tabular-nums outline-none focus:border-portal-accent';

    return (
        <div className="flex items-center gap-1.5">
            <span className="text-xs font-medium text-neutral-500">
                {label}
            </span>
            <input
                value={hour}
                onChange={(e) =>
                    push(clean(e.target.value, 12), minute, meridiem)
                }
                inputMode="numeric"
                placeholder="--"
                aria-label={`${label} hour`}
                className={box}
            />
            <span className="text-neutral-400">:</span>
            <input
                value={minute}
                onChange={(e) =>
                    push(hour, clean(e.target.value, 59), meridiem)
                }
                onBlur={() =>
                    minute !== '' &&
                    push(hour, minute.padStart(2, '0'), meridiem)
                }
                inputMode="numeric"
                placeholder="--"
                aria-label={`${label} minute`}
                className={box}
            />
            <div className="flex overflow-hidden rounded-[4px] border border-portal-line">
                {(['AM', 'PM'] as Meridiem[]).map((m) => (
                    <button
                        key={m}
                        type="button"
                        onClick={() => push(hour, minute, m)}
                        aria-pressed={meridiem === m}
                        className={cn(
                            'px-2 py-1.5 text-[11px] font-bold transition',
                            meridiem === m
                                ? 'bg-portal-accent text-white'
                                : 'bg-white text-neutral-500 hover:bg-portal-field',
                        )}
                    >
                        {m}
                    </button>
                ))}
            </div>
        </div>
    );
}

/** One logged event: icon, what it was, how it went, when. */
function EntryRow({
    entry,
    childId,
    date,
    isStaff,
}: {
    entry: PortalReportEntry;
    childId: number;
    date: string;
    isStaff: boolean;
}) {
    const Icon = ICONS[entry.type] ?? StickyNote;

    return (
        <li className="group flex items-start gap-3 py-2.5">
            <span className="mt-0.5 grid size-8 shrink-0 place-items-center rounded-[4px] bg-portal-soft text-portal-accent">
                <Icon className="size-4" />
            </span>

            <div className="min-w-0 flex-1">
                <p className="text-sm text-portal-ink">
                    <span className="font-bold capitalize">
                        {entry.label ?? entry.type}
                    </span>
                    {entry.detail && (
                        <span className="text-neutral-600">
                            {' '}
                            · {entry.detail}
                        </span>
                    )}
                    {entry.at && (
                        <span className="text-neutral-400">
                            {' '}
                            · {entry.at}
                            {entry.until && `–${entry.until}`}
                        </span>
                    )}
                </p>
                {entry.note && (
                    <p className="mt-0.5 text-sm text-neutral-500">
                        {entry.note}
                    </p>
                )}
                {entry.photos.length > 0 && (
                    <div className="mt-1.5 flex flex-wrap gap-1.5">
                        {entry.photos.map((url) => (
                            <img
                                key={url}
                                src={url}
                                alt=""
                                className="size-14 rounded-[4px] object-cover"
                            />
                        ))}
                    </div>
                )}
            </div>

            {isStaff && (
                <button
                    type="button"
                    aria-label="Remove entry"
                    onClick={() =>
                        router.delete(
                            `/portal/children/${childId}/report/entries/${entry.id}?date=${date}`,
                            { preserveScroll: true },
                        )
                    }
                    className="grid size-7 shrink-0 place-items-center rounded-[4px] text-neutral-300 opacity-0 transition group-hover:opacity-100 hover:bg-red-50 hover:text-red-500"
                >
                    <Trash2 className="size-4" />
                </button>
            )}
        </li>
    );
}

/**
 * The composer: pick a kind, fill the little that kind needs, send. Modelled on
 * a chat input rather than a form — a teacher logs in seconds, mid-room.
 */
function Composer({
    childId,
    date,
    options,
}: {
    childId: number;
    date: string;
    options: ReportOptions;
}) {
    const [kind, setKind] = useState<ReportEntryType | null>(null);
    const [label, setLabel] = useState('');
    const [detail, setDetail] = useState('');
    const [note, setNote] = useState('');
    const [from, setFrom] = useState('');
    const [to, setTo] = useState('');
    const [photos, setPhotos] = useState<string[]>([]);
    const [busy, setBusy] = useState(false);

    const open = (next: ReportEntryType) => {
        setKind(next);
        setLabel(options.labels[next]?.[0] ?? '');
        setDetail(options.details[next]?.[0] ?? '');
        setNote('');
        setFrom('');
        setTo('');
        setPhotos([]);
    };

    const close = () => setKind(null);

    const submit = (e: FormEvent) => {
        e.preventDefault();

        if (!kind) {
            return;
        }

        router.post(
            `/portal/children/${childId}/report/entries`,
            {
                type: kind,
                label: label || null,
                detail: detail || null,
                note: note || null,
                occurred_at: onDate(date, from),
                ended_at: onDate(date, to),
                photos,
                date,
            },
            {
                preserveScroll: true,
                onStart: () => setBusy(true),
                onFinish: () => setBusy(false),
                onSuccess: close,
            },
        );
    };

    const labels = kind ? (options.labels[kind] ?? []) : [];
    const details = kind ? (options.details[kind] ?? []) : [];

    return (
        <div className="p-3">
            {/* The open form sits above the kind row, like a chat attachment tray. */}
            {kind && (
                <form
                    onSubmit={submit}
                    className="mb-2 space-y-2.5 rounded-[4px] border border-portal-line p-3"
                >
                    <div className="flex items-center justify-between">
                        <p className="text-sm font-bold text-portal-ink capitalize">
                            {kind}
                        </p>
                        <button
                            type="button"
                            onClick={close}
                            aria-label="Cancel"
                            className="grid size-6 place-items-center rounded-[4px] text-neutral-400 hover:bg-portal-field"
                        >
                            <X className="size-3.5" />
                        </button>
                    </div>

                    {labels.length > 0 && (
                        <select
                            value={label}
                            onChange={(e) => setLabel(e.target.value)}
                            className="w-full rounded-[4px] border border-portal-line bg-white px-3 py-2 text-sm outline-none focus:border-portal-accent"
                        >
                            {labels.map((l) => (
                                <option key={l} value={l}>
                                    {l}
                                </option>
                            ))}
                        </select>
                    )}

                    {details.length > 0 && (
                        <div className="flex flex-wrap gap-1.5">
                            {details.map((d) => (
                                <button
                                    key={d}
                                    type="button"
                                    onClick={() => setDetail(d)}
                                    className={cn(
                                        'rounded-[4px] border px-2.5 py-1.5 text-sm font-medium transition',
                                        detail === d
                                            ? 'border-portal-accent bg-portal-accent text-white'
                                            : 'border-portal-line bg-white text-neutral-600 hover:bg-portal-field',
                                    )}
                                >
                                    {d}
                                </button>
                            ))}
                        </div>
                    )}

                    <div className="flex flex-wrap items-center gap-x-4 gap-y-2">
                        <TimeField
                            key={`${kind}-from`}
                            label={kind === 'nap' ? 'From' : 'At'}
                            onChange={setFrom}
                        />
                        {kind === 'nap' && (
                            <TimeField
                                key={`${kind}-to`}
                                label="To"
                                onChange={setTo}
                            />
                        )}
                        <span className="text-xs text-neutral-400">
                            blank = now
                        </span>
                    </div>

                    <PhotoUpload value={photos} onChange={setPhotos} max={4} />

                    <div className="flex items-end gap-2">
                        <textarea
                            value={note}
                            onChange={(e) => setNote(e.target.value)}
                            rows={2}
                            placeholder={
                                kind === 'note'
                                    ? 'What happened?'
                                    : 'Anything to add?'
                            }
                            className="flex-1 resize-none rounded-[4px] border border-portal-line px-3 py-2 text-sm outline-none focus:border-portal-accent"
                        />
                        <button
                            type="submit"
                            disabled={busy}
                            aria-label="Log it"
                            className="grid size-9 shrink-0 place-items-center rounded-[4px] bg-portal-accent text-white transition hover:brightness-95 disabled:opacity-50"
                        >
                            <Send className="size-4" />
                        </button>
                    </div>
                </form>
            )}

            <div className="flex items-center gap-1.5">
                {KINDS.map(({ type, label: text, icon: Icon }) => (
                    <button
                        key={type}
                        type="button"
                        onClick={() => (kind === type ? close() : open(type))}
                        className={cn(
                            'inline-flex flex-1 flex-col items-center gap-1 rounded-[4px] border py-2 text-[11px] font-bold transition',
                            kind === type
                                ? 'border-portal-accent bg-portal-soft text-portal-accent'
                                : 'border-portal-line text-neutral-600 hover:bg-portal-field',
                        )}
                    >
                        <Icon className="size-4" />
                        {text}
                    </button>
                ))}
            </div>
        </div>
    );
}

/**
 * One child's day as a stream: what happened, in order, with a composer at the
 * foot. Mood lives in the stream too — a child is not one mood all day.
 */
export function DaySheet({
    child,
    date,
    isStaff,
    options,
    open,
    onClose,
}: {
    child: PortalReportChild | null;
    date: string;
    isStaff: boolean;
    options: ReportOptions;
    open: boolean;
    onClose: () => void;
}) {
    const report = child?.report ?? null;
    const entries = report?.entries ?? [];
    const published = report?.published ?? false;

    /** Visibility is one boolean, so it is one handler. */
    const setVisible = (visible: boolean) => {
        if (!child) {
            return;
        }

        if (visible) {
            router.post(
                `/portal/children/${child.id}/report/publish`,
                { date },
                { preserveScroll: true },
            );

            return;
        }

        router.delete(
            `/portal/children/${child.id}/report/publish?date=${date}`,
            { preserveScroll: true },
        );
    };

    return (
        <SideModal
            hidden={open}
            onClose={onClose}
            sizeClassName="xs:max-w-[520px]"
        >
            <span title="title" className="flex items-center gap-2.5">
                <span className="font-bold text-portal-ink">
                    {child?.name ?? ''}
                </span>
                <span
                    className={cn(
                        'rounded-[4px] px-2 py-0.5 text-[10px] font-bold tracking-wide uppercase',
                        published
                            ? 'bg-emerald-50 text-emerald-600'
                            : 'bg-portal-gold/15 text-portal-gold',
                    )}
                >
                    {published ? 'Visible' : 'Hidden'}
                </span>
            </span>

            <div title="body" data-slot="body" className="!px-0 !pt-0">
                {entries.length === 0 ? (
                    <p className="py-10 text-center text-sm text-neutral-400">
                        {isStaff
                            ? 'Nothing logged yet — use the buttons below.'
                            : 'Nothing shared for today yet.'}
                    </p>
                ) : (
                    <ul className="divide-y divide-portal-line">
                        {entries.map((entry) => (
                            <EntryRow
                                key={entry.id}
                                entry={entry}
                                childId={child!.id}
                                date={date}
                                isStaff={isStaff}
                            />
                        ))}
                    </ul>
                )}
            </div>

            {isStaff && child && (
                <div title="footer" data-slot="footer">
                    <Composer
                        childId={child.id}
                        date={date}
                        options={options}
                    />

                    {/* One switch, because visibility IS one boolean. Two
                        buttons dressed a single toggle up as two actions. */}
                    <div className="border-t border-portal-line p-3">
                        <Field className="flex items-center justify-between gap-3">
                            <span className="min-w-0">
                                <Label className="block text-sm font-bold text-portal-ink">
                                    Make log visible to parent
                                </Label>
                                <Description className="block text-xs text-neutral-500">
                                    {entries.length === 0
                                        ? 'Log something first.'
                                        : published
                                          ? 'Anything you add now shows up for them straight away.'
                                          : "Parents can't see any of this yet."}
                                </Description>
                            </span>

                            <Switch
                                checked={published}
                                onChange={setVisible}
                                disabled={entries.length === 0}
                                className={cn(
                                    'group relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition',
                                    'bg-neutral-300 data-checked:bg-portal-accent',
                                    'data-disabled:cursor-not-allowed data-disabled:opacity-50',
                                )}
                            >
                                <span className="pointer-events-none inline-block size-5 translate-x-0 rounded-full bg-white shadow transition group-data-checked:translate-x-5" />
                            </Switch>
                        </Field>
                    </div>
                </div>
            )}
        </SideModal>
    );
}
