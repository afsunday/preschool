import { Head, router } from '@inertiajs/react';
import {
    Baby,
    ChevronRight,
    ClipboardList,
    Moon,
    Utensils,
} from 'lucide-react';
import { useState } from 'react';
import { cn } from '@/lib/utils';
import type {
    PortalClass,
    PortalReportChild,
    ReportOptions,
} from '@/types/portal';
import { DaySheet } from '../partials/day-sheet';

const MOOD_EMOJI: Record<string, string> = {
    Happy: '🙂',
    Content: '😌',
    Tired: '😴',
    Upset: '😢',
    Unwell: '🤒',
};

/** The last mood logged — a child's mood changes through the day, so the roster
 *  shows where they got to, not where they started. */
function latestMood(child: PortalReportChild): string | null {
    const moods = (child.report?.entries ?? []).filter(
        (e) => e.type === 'mood',
    );

    return moods.length ? (moods[moods.length - 1].detail ?? null) : null;
}

/** Total nap minutes, rendered as "2h 15m". Naps without an end are in progress. */
function napSummary(child: PortalReportChild): string | null {
    const naps = (child.report?.entries ?? []).filter((e) => e.type === 'nap');

    if (naps.length === 0) {
        return null;
    }

    const minutes = naps.reduce((total, nap) => {
        if (!nap.at || !nap.until) {
            return total;
        }

        const [sh, sm] = nap.at.split(':').map(Number);
        const [eh, em] = nap.until.split(':').map(Number);

        return total + (eh * 60 + em - (sh * 60 + sm));
    }, 0);

    if (minutes <= 0) {
        return 'napping';
    }

    const h = Math.floor(minutes / 60);
    const m = minutes % 60;

    return h > 0 ? `${h}h${m ? ` ${m}m` : ''}` : `${m}m`;
}

/** One line per child: enough to scan the room without opening anything. */
function ChildRow({
    child,
    isStaff,
    onOpen,
}: {
    child: PortalReportChild;
    isStaff: boolean;
    onOpen: (child: PortalReportChild) => void;
}) {
    const report = child.report;
    const entries = report?.entries ?? [];
    const meals = entries.filter((e) => e.type === 'meal').length;
    const nappies = entries.filter((e) => e.type === 'nappy').length;
    const nap = napSummary(child);
    const mood = latestMood(child);
    const logged = entries.length > 0;

    return (
        <button
            type="button"
            onClick={() => onOpen(child)}
            className="flex w-full items-center gap-3 rounded-[4px] border border-portal-line bg-white px-4 py-3 text-left transition hover:bg-portal-field"
        >
            {child.photo ? (
                <img
                    src={child.photo}
                    alt=""
                    className="size-11 shrink-0 rounded-full object-cover"
                />
            ) : (
                <span className="grid size-11 shrink-0 place-items-center rounded-full bg-portal-soft text-base font-bold text-portal-accent">
                    {child.name.charAt(0)}
                </span>
            )}

            <div className="min-w-0 flex-1">
                <p className="truncate text-[15px] font-bold text-portal-ink">
                    {child.name}
                    {mood && (
                        <span className="ml-1.5">{MOOD_EMOJI[mood] ?? ''}</span>
                    )}
                </p>

                {logged ? (
                    <span className="mt-0.5 flex flex-wrap items-center gap-3 text-sm text-neutral-500">
                        {nap && (
                            <span className="inline-flex items-center gap-1">
                                <Moon className="size-3.5" /> {nap}
                            </span>
                        )}
                        {meals > 0 && (
                            <span className="inline-flex items-center gap-1">
                                <Utensils className="size-3.5" /> {meals}
                            </span>
                        )}
                        {nappies > 0 && (
                            <span className="inline-flex items-center gap-1">
                                <Baby className="size-3.5" /> {nappies}
                            </span>
                        )}
                    </span>
                ) : (
                    <span className="mt-0.5 block text-sm text-neutral-400">
                        {isStaff ? 'Nothing logged yet' : 'Nothing shared yet'}
                    </span>
                )}
            </div>

            {/* Everything logged is saved — this says whether the parent can
                see it, not whether it is finished. */}
            {isStaff && logged && (
                <span
                    className={cn(
                        'shrink-0 rounded-[4px] px-2 py-1 text-[11px] font-bold tracking-wide uppercase',
                        report?.published
                            ? 'bg-emerald-50 text-emerald-600'
                            : 'bg-portal-gold/15 text-portal-gold',
                    )}
                >
                    {report?.published ? 'Visible' : 'Hidden'}
                </span>
            )}

            <ChevronRight className="size-5 shrink-0 text-neutral-300" />
        </button>
    );
}

export default function ClassToday({
    classroom,
    children,
    date,
    isStaff,
    options,
}: {
    classroom: PortalClass;
    children: PortalReportChild[];
    date: string;
    isStaff: boolean;
    options: ReportOptions;
}) {
    const [open, setOpen] = useState<PortalReportChild | null>(null);

    // Keep the open sheet in sync with fresh props after a log/send.
    const active = open
        ? (children.find((c) => c.id === open.id) ?? null)
        : null;

    return (
        <>
            <Head title={`${classroom.name} · Today`} />
            <div className="py-5">
                <div className="flex flex-wrap items-center justify-between gap-3 pb-3">
                    <h2 className="text-xl font-bold text-portal-ink">Today</h2>

                    <div className="flex items-center gap-2">
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
                            className="rounded-[4px] border border-portal-line px-3 py-2 text-sm text-neutral-600 outline-none"
                        />
                    </div>
                </div>

                {children.length === 0 ? (
                    <div className="grid place-items-center rounded-[4px] border border-dashed border-portal-line bg-white px-4 py-14 text-center">
                        <ClipboardList className="size-8 text-neutral-300" />
                        <p className="mt-3 text-[15px] font-bold text-portal-ink">
                            Nothing to show
                        </p>
                    </div>
                ) : (
                    <div className="space-y-2">
                        {children.map((child) => (
                            <ChildRow
                                key={child.id}
                                child={child}
                                isStaff={isStaff}
                                onOpen={setOpen}
                            />
                        ))}
                    </div>
                )}
            </div>

            <DaySheet
                child={active}
                date={date}
                isStaff={isStaff}
                options={options}
                open={active !== null}
                onClose={() => setOpen(null)}
            />
        </>
    );
}
