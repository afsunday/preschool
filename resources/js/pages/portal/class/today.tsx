import { Head, router } from '@inertiajs/react';
import {
    Baby,
    ClipboardList,
    Moon,
    Send,
    StickyNote,
    Utensils,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type {
    PortalClass,
    PortalReportChild,
    PortalReportEntry,
    ReportEntryType,
} from '@/types/portal';

const ENTRY_ICONS: Record<ReportEntryType, LucideIcon> = {
    nap: Moon,
    meal: Utensils,
    nappy: Baby,
    note: StickyNote,
    photo: StickyNote,
};

const MOODS: { value: string; label: string }[] = [
    { value: 'happy', label: '🙂 Happy' },
    { value: 'ok', label: '😐 OK' },
    { value: 'tired', label: '😴 Tired' },
    { value: 'sad', label: '🙁 Sad' },
];

/** One event on the day — a nap range, a meal, a nappy, a note. */
function EntryRow({ entry }: { entry: PortalReportEntry }) {
    const Icon = ENTRY_ICONS[entry.type] ?? StickyNote;

    return (
        <li className="flex items-start gap-2.5 py-1.5">
            <Icon className="mt-0.5 size-4 shrink-0 text-portal-accent" />
            <div className="min-w-0 flex-1 text-sm">
                <span className="font-medium text-portal-ink capitalize">
                    {entry.type}
                </span>
                {entry.detail && (
                    <span className="text-neutral-600"> · {entry.detail}</span>
                )}
                {entry.at && (
                    <span className="text-neutral-400">
                        {' '}
                        · {entry.at}
                        {entry.until && ` – ${entry.until}`}
                    </span>
                )}
                {entry.note && (
                    <p className="text-xs text-neutral-500">{entry.note}</p>
                )}
                {entry.photos.length > 0 && (
                    <div className="mt-1.5 flex flex-wrap gap-1.5">
                        {entry.photos.map((p) => (
                            <img
                                key={p.id}
                                src={p.url}
                                alt=""
                                className="size-14 rounded-[4px] object-cover"
                            />
                        ))}
                    </div>
                )}
            </div>
        </li>
    );
}

function ChildReport({
    child,
    date,
    isStaff,
}: {
    child: PortalReportChild;
    date: string;
    isStaff: boolean;
}) {
    const report = child.report;

    const addEntry = (type: ReportEntryType, detail?: string) =>
        router.post(
            `/portal/children/${child.id}/report/entries`,
            { type, detail, date },
            { preserveScroll: true },
        );

    return (
        <div className="rounded-[6px] border border-portal-line bg-white p-4">
            <div className="flex items-center gap-3">
                {child.photo ? (
                    <img
                        src={child.photo}
                        alt={child.name}
                        className="size-10 shrink-0 rounded-full object-cover"
                    />
                ) : (
                    <span className="grid size-10 shrink-0 place-items-center rounded-full bg-portal-accent/10 text-sm font-bold text-portal-accent">
                        {child.name.charAt(0)}
                    </span>
                )}
                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-bold text-portal-ink">
                        {child.name}
                    </p>
                    {report && !report.published && (
                        <span className="text-[11px] font-semibold tracking-wide text-portal-gold uppercase">
                            Draft — not sent
                        </span>
                    )}
                </div>

                {isStaff && report && !report.published && (
                    <button
                        type="button"
                        onClick={() =>
                            router.post(
                                `/portal/children/${child.id}/report/publish`,
                                { date },
                                { preserveScroll: true },
                            )
                        }
                        className="inline-flex items-center gap-1.5 rounded-full bg-portal-accent px-3 py-1.5 text-[11px] font-bold tracking-wide text-white uppercase transition hover:brightness-95"
                    >
                        <Send className="size-3" />
                        Send to parent
                    </button>
                )}
            </div>

            {report === null ? (
                <p className="mt-3 text-sm text-neutral-400">
                    {isStaff
                        ? 'Nothing logged yet today.'
                        : "Today's report hasn't been sent yet."}
                </p>
            ) : (
                <>
                    {report.mood && (
                        <p className="mt-3 text-sm text-neutral-600">
                            Mood:{' '}
                            <span className="font-medium text-portal-ink capitalize">
                                {report.mood}
                            </span>
                        </p>
                    )}
                    {report.summary && (
                        <p className="mt-1 text-sm text-neutral-700">
                            {report.summary}
                        </p>
                    )}
                    {report.entries.length > 0 && (
                        <ul className="mt-2 divide-y divide-portal-line">
                            {report.entries.map((entry) => (
                                <EntryRow key={entry.id} entry={entry} />
                            ))}
                        </ul>
                    )}
                </>
            )}

            {/* Quick-log: the teacher taps these across the day. */}
            {isStaff && (
                <div className="mt-3 flex flex-wrap gap-1.5 border-t border-portal-line pt-3">
                    <button
                        type="button"
                        onClick={() => addEntry('nap')}
                        className="inline-flex items-center gap-1.5 rounded-full border border-portal-line px-2.5 py-1 text-xs text-neutral-600 transition hover:bg-neutral-50"
                    >
                        <Moon className="size-3.5" /> Nap
                    </button>
                    <button
                        type="button"
                        onClick={() => addEntry('meal', 'Ate all')}
                        className="inline-flex items-center gap-1.5 rounded-full border border-portal-line px-2.5 py-1 text-xs text-neutral-600 transition hover:bg-neutral-50"
                    >
                        <Utensils className="size-3.5" /> Meal
                    </button>
                    <button
                        type="button"
                        onClick={() => addEntry('nappy', 'Wet')}
                        className="inline-flex items-center gap-1.5 rounded-full border border-portal-line px-2.5 py-1 text-xs text-neutral-600 transition hover:bg-neutral-50"
                    >
                        <Baby className="size-3.5" /> Nappy
                    </button>

                    <select
                        value={report?.mood ?? ''}
                        onChange={(e) =>
                            router.patch(
                                `/portal/children/${child.id}/report`,
                                { mood: e.target.value || null, date },
                                { preserveScroll: true },
                            )
                        }
                        className="ml-auto rounded-full border border-portal-line px-2.5 py-1 text-xs text-neutral-600 outline-none"
                    >
                        <option value="">Mood…</option>
                        {MOODS.map((m) => (
                            <option key={m.value} value={m.value}>
                                {m.label}
                            </option>
                        ))}
                    </select>
                </div>
            )}
        </div>
    );
}

export default function ClassToday({
    classroom,
    children,
    date,
    isStaff,
}: {
    classroom: PortalClass;
    children: PortalReportChild[];
    date: string;
    isStaff: boolean;
}) {
    return (
        <>
            <Head title={`${classroom.name} · Today`} />
            <div className="py-5">
                <div className="flex items-center justify-between pb-3">
                    <h2 className="text-lg font-bold text-portal-ink">
                        Today
                        <span className="ml-2 text-sm font-normal text-neutral-400">
                            {date}
                        </span>
                    </h2>
                    <input
                        type="date"
                        value={date}
                        onChange={(e) =>
                            router.get(
                                `/portal/classes/${classroom.id}/today`,
                                { date: e.target.value },
                                { preserveState: true },
                            )
                        }
                        className="rounded-[4px] border border-portal-line px-2 py-1 text-xs text-neutral-600 outline-none"
                    />
                </div>

                {children.length === 0 ? (
                    <div className="grid place-items-center rounded-[6px] border border-dashed border-portal-line bg-white px-4 py-14 text-center">
                        <ClipboardList className="size-8 text-neutral-300" />
                        <p className="mt-3 text-sm font-medium text-portal-ink">
                            Nothing to show
                        </p>
                    </div>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2">
                        {children.map((child) => (
                            <ChildReport
                                key={child.id}
                                child={child}
                                date={date}
                                isStaff={isStaff}
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
