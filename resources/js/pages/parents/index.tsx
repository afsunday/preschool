import { Head } from '@inertiajs/react';
import { BadgeCheck, Search, Users } from 'lucide-react';
import { useMemo, useState } from 'react';

type Child = {
    id: number;
    name: string;
    classroom: string | null;
};

type Parent = {
    id: number;
    name: string;
    email: string;
    verified: boolean;
    joinedAt: string | null;
    children: Child[];
};

export default function ParentsIndex({ parents }: { parents: Parent[] }) {
    const [query, setQuery] = useState('');

    const filtered = useMemo(() => {
        const q = query.trim().toLowerCase();
        if (!q) {
            return parents;
        }
        return parents.filter(
            (p) =>
                p.name.toLowerCase().includes(q) ||
                p.email.toLowerCase().includes(q) ||
                p.children.some((c) => c.name.toLowerCase().includes(q)),
        );
    }, [parents, query]);

    return (
        <>
            <Head title="Parents" />

            <div className="flex h-full flex-col gap-6 p-4">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 className="text-xl font-semibold tracking-tight">
                            Parents
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Every family and the children they’re linked to.
                        </p>
                    </div>
                    <label className="relative">
                        <Search className="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-neutral-400" />
                        <input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Search parents or children"
                            className="form-control w-64 pl-8"
                        />
                    </label>
                </div>

                {filtered.length === 0 ? (
                    <div className="grid place-items-center rounded-[4px] border border-dashed border-black/10 px-4 py-16 text-center">
                        <Users className="size-8 text-neutral-300" />
                        <p className="mt-3 text-[15px] font-semibold">
                            {parents.length === 0
                                ? 'No parents yet'
                                : 'No matches'}
                        </p>
                        <p className="mt-1 max-w-sm text-sm text-neutral-500">
                            {parents.length === 0
                                ? 'Parents appear here once they register and redeem their child’s invite code.'
                                : 'Try a different name or email.'}
                        </p>
                    </div>
                ) : (
                    <div className="overflow-hidden rounded-[4px] border border-black/10">
                        <table className="w-full text-sm">
                            <thead className="bg-neutral-50 text-left text-xs text-neutral-500 uppercase">
                                <tr>
                                    <th className="px-4 py-2 font-medium">
                                        Parent
                                    </th>
                                    <th className="px-4 py-2 font-medium">
                                        Children
                                    </th>
                                    <th className="px-4 py-2 font-medium">
                                        Joined
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-black/5">
                                {filtered.map((p) => (
                                    <tr key={p.id} className="hover:bg-neutral-50">
                                        <td className="px-4 py-3 align-top">
                                            <div className="flex items-center gap-1.5 font-medium">
                                                {p.name}
                                                {p.verified && (
                                                    <BadgeCheck
                                                        className="size-4 text-emerald-500"
                                                        aria-label="Email verified"
                                                    />
                                                )}
                                            </div>
                                            <div className="text-neutral-500">
                                                {p.email}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 align-top">
                                            {p.children.length === 0 ? (
                                                <span className="text-xs text-neutral-400">
                                                    No children linked yet
                                                </span>
                                            ) : (
                                                <div className="flex flex-wrap gap-1.5">
                                                    {p.children.map((c) => (
                                                        <span
                                                            key={c.id}
                                                            className="inline-flex items-center gap-1 rounded-[4px] bg-neutral-100 px-2 py-0.5 text-xs"
                                                        >
                                                            <span className="font-medium">
                                                                {c.name}
                                                            </span>
                                                            {c.classroom && (
                                                                <span className="text-neutral-500">
                                                                    · {c.classroom}
                                                                </span>
                                                            )}
                                                        </span>
                                                    ))}
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 align-top text-neutral-500">
                                            {p.joinedAt}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}

ParentsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Parents', href: '/admin/parents' },
    ],
};
