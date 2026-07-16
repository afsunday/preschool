import { Head, useForm } from '@inertiajs/react';
import { KeyRound, Loader2 } from 'lucide-react';
import type { FormEvent } from 'react';

interface LinkedChild {
    id: number;
    name: string;
    classroom: string | null;
}

const RELATIONSHIPS = [
    { value: 'mum', label: 'Mum' },
    { value: 'dad', label: 'Dad' },
    { value: 'guardian', label: 'Guardian' },
];

/**
 * Redeem a child's invite code. This is how a parent joins — and how a parent
 * with several children adds each one: one code per child.
 */
export default function PortalJoin({ children }: { children: LinkedChild[] }) {
    const form = useForm({ code: '', relationship: 'mum' });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post('/portal/join');
    };

    return (
        <>
            <Head title="Add a child" />
            <div className="mx-auto max-w-md py-10">
                <div className="rounded-[4px] border border-portal-line bg-white p-6 shadow-s1">
                    <span className="grid size-11 place-items-center rounded-[4px] bg-portal-soft text-portal-accent">
                        <KeyRound className="size-5" />
                    </span>
                    <h1 className="mt-4 text-xl font-bold text-portal-ink">
                        Add your child
                    </h1>
                    <p className="mt-1 text-sm text-neutral-500">
                        Enter the invite code the school gave you. Have more
                        than one child? Add them one code at a time.
                    </p>

                    <form onSubmit={submit} className="mt-5 space-y-4">
                        <div>
                            <label
                                htmlFor="code"
                                className="mb-1 block text-xs font-medium text-neutral-600"
                            >
                                Invite code
                            </label>
                            <input
                                id="code"
                                value={form.data.code}
                                onChange={(e) =>
                                    form.setData(
                                        'code',
                                        e.target.value.toUpperCase(),
                                    )
                                }
                                placeholder="e.g. TUNDE001"
                                autoComplete="off"
                                className="w-full rounded-[4px] border border-portal-line px-3 py-2.5 text-center font-mono text-lg tracking-widest text-portal-ink uppercase outline-none focus:border-portal-accent"
                            />
                            {form.errors.code && (
                                <p className="mt-1 text-xs text-red-500">
                                    {form.errors.code}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="relationship"
                                className="mb-1 block text-xs font-medium text-neutral-600"
                            >
                                You are their…
                            </label>
                            <select
                                id="relationship"
                                value={form.data.relationship}
                                onChange={(e) =>
                                    form.setData('relationship', e.target.value)
                                }
                                className="w-full rounded-[4px] border border-portal-line px-3 py-2.5 text-sm outline-none focus:border-portal-accent"
                            >
                                {RELATIONSHIPS.map((r) => (
                                    <option key={r.value} value={r.value}>
                                        {r.label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <button
                            type="submit"
                            disabled={
                                form.processing || form.data.code.trim() === ''
                            }
                            className="inline-flex w-full items-center justify-center gap-2 rounded-[4px] bg-portal-accent px-4 py-2.5 text-sm font-bold text-white transition hover:brightness-95 disabled:opacity-50"
                        >
                            {form.processing && (
                                <Loader2 className="size-4 animate-spin" />
                            )}
                            Link my child
                        </button>
                    </form>
                </div>

                {children.length > 0 && (
                    <div className="mt-5">
                        <p className="pb-2 text-xs font-bold tracking-wide text-neutral-400 uppercase">
                            Already linked
                        </p>
                        <div className="space-y-2">
                            {children.map((child) => (
                                <div
                                    key={child.id}
                                    className="flex items-center gap-3 rounded-[4px] border border-portal-line bg-white px-3 py-2.5"
                                >
                                    <span className="grid size-8 shrink-0 place-items-center rounded-full bg-portal-soft text-xs font-bold text-portal-accent">
                                        {child.name.charAt(0)}
                                    </span>
                                    <span className="min-w-0 flex-1">
                                        <span className="block truncate text-sm font-semibold text-portal-ink">
                                            {child.name}
                                        </span>
                                        <span className="block truncate text-xs text-neutral-500">
                                            {child.classroom}
                                        </span>
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}
