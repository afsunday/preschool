import { Description, Field, Label, Switch } from '@headlessui/react';
import { router, useForm } from '@inertiajs/react';
import { Download, FileText, Loader2, Plus, Trash2, X } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import SideModal from '@/components/side-modal';
import { cn } from '@/lib/utils';
import type { PortalChild, PortalReportCard } from '@/types/portal';

function fileSize(bytes: number): string {
    return bytes >= 1_048_576
        ? `${(bytes / 1_048_576).toFixed(1)} MB`
        : `${Math.max(1, Math.round(bytes / 1024))} KB`;
}

/** One card: the document, when it was issued, and who can see it. */
function CardRow({
    card,
    childId,
    isStaff,
}: {
    card: PortalReportCard;
    childId: number;
    isStaff: boolean;
}) {
    return (
        <li className="group rounded-[4px] border border-portal-line p-3">
            <div className="flex items-start gap-3">
                <span className="mt-0.5 grid size-9 shrink-0 place-items-center rounded-[4px] bg-portal-soft text-portal-accent">
                    <FileText className="size-4.5" />
                </span>

                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-bold text-portal-ink">
                        {card.title}
                    </p>
                    <p className="truncate text-xs text-neutral-500">
                        {[card.issuedOn, card.file && fileSize(card.file.size)]
                            .filter(Boolean)
                            .join(' · ')}
                    </p>
                    {card.note && (
                        <p className="mt-1 text-sm text-neutral-600">
                            {card.note}
                        </p>
                    )}
                </div>

                <div className="flex shrink-0 items-center gap-1">
                    {card.file && (
                        <a
                            href={card.file.url}
                            download
                            target="_blank"
                            rel="noreferrer"
                            aria-label={`Open ${card.title}`}
                            className="grid size-8 place-items-center rounded-[4px] text-neutral-400 transition hover:bg-portal-field hover:text-portal-ink"
                        >
                            <Download className="size-4" />
                        </a>
                    )}
                    {isStaff && (
                        <button
                            type="button"
                            aria-label={`Delete ${card.title}`}
                            onClick={() =>
                                router.delete(
                                    `/portal/children/${childId}/report-cards/${card.id}`,
                                    { preserveScroll: true },
                                )
                            }
                            className="grid size-8 place-items-center rounded-[4px] text-neutral-300 opacity-0 transition group-hover:opacity-100 hover:bg-red-50 hover:text-red-500"
                        >
                            <Trash2 className="size-4" />
                        </button>
                    )}
                </div>
            </div>

            {/* Same control as the day log: one boolean, one switch. */}
            {isStaff && (
                <Field className="mt-3 flex items-center justify-between gap-3 border-t border-portal-line pt-3">
                    <span>
                        <Label className="block text-xs font-bold text-portal-ink">
                            Visible to parent
                        </Label>
                        <Description className="block text-xs text-neutral-500">
                            {card.published
                                ? 'The parent can open this.'
                                : 'Uploaded, but not shared yet.'}
                        </Description>
                    </span>
                    <Switch
                        checked={card.published}
                        onChange={(on) =>
                            router.patch(
                                `/portal/children/${childId}/report-cards/${card.id}`,
                                { published: on },
                                { preserveScroll: true },
                            )
                        }
                        className="group relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-neutral-300 transition data-checked:bg-portal-accent"
                    >
                        <span className="pointer-events-none inline-block size-4 translate-x-0 rounded-full bg-white shadow transition group-data-checked:translate-x-4" />
                    </Switch>
                </Field>
            )}
        </li>
    );
}

/**
 * Upload a new card. A plain file input, not the media library — a report card
 * is private to one family and has no business in the site's asset picker.
 */
function UploadCard({ childId }: { childId: number }) {
    const [open, setOpen] = useState(false);
    const form = useForm<{
        title: string;
        issued_on: string;
        note: string;
        file: File | null;
    }>({
        title: '',
        issued_on: '',
        note: '',
        file: null,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post(`/portal/children/${childId}/report-cards`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setOpen(false);
            },
        });
    };

    if (!open) {
        return (
            <button
                type="button"
                onClick={() => setOpen(true)}
                className="inline-flex w-full items-center justify-center gap-2 rounded-[4px] border border-dashed border-portal-line py-2.5 text-sm font-bold text-neutral-600 transition hover:bg-portal-field"
            >
                <Plus className="size-4" />
                Upload a report card
            </button>
        );
    }

    return (
        <form
            onSubmit={submit}
            className="space-y-3 rounded-[4px] border border-portal-line p-3"
        >
            <div className="flex items-center justify-between">
                <p className="text-sm font-bold text-portal-ink">
                    New report card
                </p>
                <button
                    type="button"
                    onClick={() => setOpen(false)}
                    aria-label="Cancel"
                    className="grid size-6 place-items-center rounded-[4px] text-neutral-400 hover:bg-portal-field"
                >
                    <X className="size-3.5" />
                </button>
            </div>

            <div>
                <input
                    value={form.data.title}
                    onChange={(e) => form.setData('title', e.target.value)}
                    placeholder="Term 1 · 2026/2027"
                    className="w-full rounded-[4px] border border-portal-line px-3 py-2 text-sm outline-none focus:border-portal-accent"
                />
                {form.errors.title && (
                    <p className="mt-1 text-xs text-red-500">
                        {form.errors.title}
                    </p>
                )}
            </div>

            <label className="block">
                <span className="mb-1 block text-xs font-medium text-neutral-500">
                    Issued on
                </span>
                <input
                    type="date"
                    value={form.data.issued_on}
                    onChange={(e) => form.setData('issued_on', e.target.value)}
                    className="rounded-[4px] border border-portal-line px-3 py-2 text-sm outline-none focus:border-portal-accent"
                />
            </label>

            <div>
                <span className="mb-1 block text-xs font-medium text-neutral-500">
                    The document
                </span>
                <input
                    type="file"
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                    onChange={(e) =>
                        form.setData('file', e.target.files?.[0] ?? null)
                    }
                    className="w-full rounded-[4px] border border-portal-line px-3 py-2 text-sm file:mr-3 file:rounded-[4px] file:border-0 file:bg-portal-field file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-portal-ink"
                />
                <p className="mt-1 text-xs text-neutral-400">
                    PDF, image or Word. Up to 10 MB.
                </p>
                {form.errors.file && (
                    <p className="mt-1 text-xs text-red-500">
                        {form.errors.file}
                    </p>
                )}
            </div>

            <textarea
                value={form.data.note}
                onChange={(e) => form.setData('note', e.target.value)}
                rows={2}
                placeholder="A note for the parent (optional)"
                className="w-full resize-none rounded-[4px] border border-portal-line px-3 py-2 text-sm outline-none focus:border-portal-accent"
            />

            <button
                type="submit"
                disabled={
                    form.processing ||
                    form.data.title.trim() === '' ||
                    form.data.file === null
                }
                className="inline-flex w-full items-center justify-center gap-2 rounded-[4px] bg-portal-accent py-2.5 text-sm font-bold text-white transition hover:brightness-95 disabled:bg-portal-field disabled:text-neutral-400"
            >
                {form.processing && <Loader2 className="size-4 animate-spin" />}
                Upload
            </button>
        </form>
    );
}

/**
 * A child's record: who their family is, and their report cards.
 *
 * A parent sees only their own child's shared cards — the controller decides
 * that, this just renders what it is given.
 */
export function ChildSheet({
    child,
    isStaff,
    canManage,
    open,
    onClose,
}: {
    child: PortalChild | null;
    isStaff: boolean;
    canManage: boolean;
    open: boolean;
    onClose: () => void;
}) {
    const cards = child?.reportCards ?? [];

    return (
        <SideModal
            hidden={open}
            onClose={onClose}
            sizeClassName="xs:max-w-[480px]"
        >
            <span title="title" className="flex items-center gap-2.5">
                <span className="font-bold text-portal-ink">
                    {child?.name ?? ''}
                </span>
                {child?.isMine && (
                    <span className="rounded-[4px] bg-portal-gold/15 px-2 py-0.5 text-[10px] font-bold tracking-wide text-portal-gold uppercase">
                        Yours
                    </span>
                )}
            </span>

            <div title="body" data-slot="body" className="space-y-5">
                <div>
                    <p className="mb-2 text-sm font-bold text-portal-ink">
                        Family
                    </p>
                    {child && child.guardians.length > 0 ? (
                        <div className="flex flex-wrap gap-1.5">
                            {child.guardians.map((g) => (
                                <span
                                    key={g.id}
                                    className="rounded-[4px] bg-portal-field px-2 py-1 text-xs text-neutral-600"
                                >
                                    {g.name}
                                    {g.relationship && (
                                        <span className="text-neutral-400">
                                            {' '}
                                            · {g.relationship}
                                        </span>
                                    )}
                                </span>
                            ))}
                        </div>
                    ) : (
                        <p className="text-sm text-neutral-400">
                            No parent linked yet.
                        </p>
                    )}

                    {canManage && child?.inviteCode && (
                        <p className="mt-2 text-xs text-neutral-500">
                            Invite code:{' '}
                            <span className="font-mono font-bold text-portal-ink">
                                {child.inviteCode}
                            </span>
                        </p>
                    )}
                </div>

                <div>
                    <p className="mb-2 text-sm font-bold text-portal-ink">
                        Report cards
                        <span className="ml-1.5 font-normal text-neutral-400">
                            {cards.length}
                        </span>
                    </p>

                    {cards.length === 0 ? (
                        <p
                            className={cn(
                                'text-sm text-neutral-400',
                                isStaff && 'mb-3',
                            )}
                        >
                            {isStaff
                                ? 'None uploaded yet.'
                                : 'Nothing shared yet.'}
                        </p>
                    ) : (
                        <ul className="mb-3 space-y-2">
                            {cards.map((card) => (
                                <CardRow
                                    key={card.id}
                                    card={card}
                                    childId={child!.id}
                                    isStaff={isStaff}
                                />
                            ))}
                        </ul>
                    )}

                    {isStaff && child && <UploadCard childId={child.id} />}
                </div>
            </div>
        </SideModal>
    );
}
