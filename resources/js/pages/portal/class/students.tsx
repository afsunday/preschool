import { Head } from '@inertiajs/react';
import { Check, Copy, Users } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import type { PortalChild, PortalClass } from '@/types/portal';

/**
 * The handoff point of the whole parent-link system: an admin copies this code
 * and gives it to the parent, who redeems it at /portal/join.
 */
function InviteCode({ code }: { code: string }) {
    const [copied, setCopied] = useState(false);

    const copy = async () => {
        await navigator.clipboard.writeText(code);
        setCopied(true);
        toast.success('Invite code copied — send it to the parent.');
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <button
            type="button"
            onClick={copy}
            title="Copy this code and give it to a parent to link them to this child"
            className="hidden items-center gap-1.5 rounded-[4px] border border-portal-line px-2.5 py-1 font-mono text-xs text-neutral-600 transition hover:bg-neutral-50 sm:flex"
        >
            {copied ? (
                <Check className="size-3.5 text-emerald-500" />
            ) : (
                <Copy className="size-3.5 text-neutral-400" />
            )}
            {code}
        </button>
    );
}

/**
 * The roster. A child here has no login — the record exists so staff know which
 * parents belong to the room, which is what the guardian chips show.
 */
function ChildRow({
    child,
    canManage,
}: {
    child: PortalChild;
    canManage: boolean;
}) {
    return (
        <div className="flex items-center gap-3 rounded-[4px] border border-portal-line bg-white px-3 py-3">
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
                <p className="truncate text-sm font-semibold text-portal-ink">
                    {child.name}
                    {child.isMine && (
                        <span className="ml-2 rounded-[3px] bg-portal-gold/15 px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-portal-gold uppercase">
                            Yours
                        </span>
                    )}
                </p>
                <div className="mt-0.5 flex flex-wrap items-center gap-1.5">
                    {child.guardians.length === 0 ? (
                        <span className="text-xs text-neutral-400">
                            No parent linked yet
                        </span>
                    ) : (
                        child.guardians.map((g) => (
                            <span
                                key={g.id}
                                className="rounded-[3px] bg-neutral-100 px-1.5 py-0.5 text-[11px] text-neutral-600"
                            >
                                {g.name}
                                {g.relationship && (
                                    <span className="text-neutral-400">
                                        {' '}
                                        · {g.relationship}
                                    </span>
                                )}
                            </span>
                        ))
                    )}
                </div>
            </div>

            {/* The invite code IS the parent-link system: give it to a parent and
                they attach themselves on sign-up. Admin-only. */}
            {canManage && child.inviteCode && (
                <InviteCode code={child.inviteCode} />
            )}
        </div>
    );
}

export default function ClassStudents({
    classroom,
    children,
    canManage,
}: {
    classroom: PortalClass;
    children: PortalChild[];
    canManage: boolean;
}) {
    return (
        <>
            <Head title={`${classroom.name} · Students`} />
            <div className="py-5">
                <div className="flex items-center justify-between pb-3">
                    <h2 className="text-lg font-bold text-portal-ink">
                        Students
                        <span className="ml-2 text-sm font-normal text-neutral-400">
                            {children.length}
                        </span>
                    </h2>
                </div>

                {children.length === 0 ? (
                    <div className="grid place-items-center rounded-[4px] border border-dashed border-portal-line bg-white px-4 py-14 text-center">
                        <Users className="size-8 text-neutral-300" />
                        <p className="mt-3 text-sm font-medium text-portal-ink">
                            No students in this room
                        </p>
                        <p className="mt-1 text-xs text-neutral-500">
                            An admin adds students to a class.
                        </p>
                    </div>
                ) : (
                    <div className="space-y-2">
                        {children.map((child) => (
                            <ChildRow
                                key={child.id}
                                child={child}
                                canManage={canManage}
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
