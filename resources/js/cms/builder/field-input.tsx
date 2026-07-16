import { Plus, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { MediaItem } from '@/cms/media';
import { createHttpMediaApi, MediaPicker } from '@/cms/media';
import RichTextEditor from '@/components/rich-text-editor';
import type { FieldSchema } from './types';

const mediaApi = createHttpMediaApi('/admin/media/items');

export function FieldInput({
    field,
    value,
    onChange,
}: {
    field: FieldSchema;
    value: unknown;
    onChange: (value: unknown) => void;
}) {
    return (
        <label className="block">
            <span className="mb-1 block text-xs font-medium text-neutral-600">
                {field.label}
                {field.required && <span className="text-red-500"> *</span>}
            </span>
            <Control field={field} value={value} onChange={onChange} />
        </label>
    );
}

function Control({
    field,
    value,
    onChange,
}: {
    field: FieldSchema;
    value: unknown;
    onChange: (value: unknown) => void;
}) {
    switch (field.type) {
        case 'textarea':
            return (
                <textarea
                    className="form-control"
                    rows={3}
                    value={(value as string) ?? ''}
                    onChange={(e) => onChange(e.target.value)}
                />
            );

        case 'richtext':
            return (
                <RichTextEditor
                    value={(value as string) ?? ''}
                    onChange={onChange}
                    minHeight="160px"
                />
            );

        case 'number':
            return (
                <input
                    type="number"
                    className="form-control"
                    value={(value as number | string) ?? ''}
                    onChange={(e) =>
                        onChange(
                            e.target.value === ''
                                ? null
                                : Number(e.target.value),
                        )
                    }
                />
            );

        case 'url':
            return (
                <input
                    type="url"
                    className="form-control"
                    value={(value as string) ?? ''}
                    onChange={(e) => onChange(e.target.value)}
                />
            );

        case 'color':
            return (
                <input
                    type="color"
                    className="h-9 w-16 rounded-[4px] border border-black/20"
                    value={(value as string) ?? '#000000'}
                    onChange={(e) => onChange(e.target.value)}
                />
            );

        case 'select':
            return (
                <select
                    className="form-select"
                    value={(value as string) ?? ''}
                    onChange={(e) => onChange(e.target.value)}
                >
                    <option value="">—</option>
                    {field.options?.map((o) => (
                        <option key={o.value} value={o.value}>
                            {o.label}
                        </option>
                    ))}
                </select>
            );

        case 'media':
            return (
                <MediaField
                    value={value as number | null}
                    kind={field.kind}
                    onChange={onChange}
                />
            );

        case 'repeater':
            return (
                <RepeaterField
                    field={field}
                    value={(value as Record<string, unknown>[]) ?? []}
                    onChange={onChange}
                />
            );

        case 'relation':
            return (
                <RelationField
                    field={field}
                    value={value as string | number | null}
                    onChange={onChange}
                />
            );

        default:
            return (
                <input
                    type="text"
                    className="form-control"
                    value={(value as string) ?? ''}
                    onChange={(e) => onChange(e.target.value)}
                />
            );
    }
}

/** Stores a media id; resolves it back to an item for the picker thumbnail. */
function MediaField({
    value,
    kind,
    onChange,
}: {
    value: number | null;
    kind?: string;
    onChange: (id: number | null) => void;
}) {
    const [item, setItem] = useState<MediaItem | null>(null);

    useEffect(() => {
        if (value == null) {
            return;
        }

        let active = true;

        fetch(`/admin/media/items/${value}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then((r) => (r.ok ? r.json() : null))
            .then((j) => {
                if (active) {
                    setItem(j?.data ?? null);
                }
            })
            .catch(() => {});

        return () => {
            active = false;
        };
    }, [value]);

    // Derived, not stored: the fetched item counts only once it matches the
    // current id. Avoids setState-in-effect, and means a stale item can never be
    // shown for a new value.
    const resolved = value != null && item?.id === value ? item : null;

    return (
        <MediaPicker
            api={mediaApi}
            value={resolved}
            hasValue={value != null}
            loading={value != null && resolved === null}
            kind={kind as MediaItem['kind'] | undefined}
            onChange={(picked) => onChange(picked?.id ?? null)}
        />
    );
}

function RepeaterField({
    field,
    value,
    onChange,
}: {
    field: FieldSchema;
    value: Record<string, unknown>[];
    onChange: (rows: Record<string, unknown>[]) => void;
}) {
    const setRow = (i: number, key: string, v: unknown) => {
        const next = value.map((row, idx) =>
            idx === i ? { ...row, [key]: v } : row,
        );
        onChange(next);
    };

    return (
        <div className="space-y-3">
            {value.map((row, i) => (
                <div
                    key={i}
                    className="space-y-2 rounded-[4px] border border-black/20 p-3"
                >
                    <div className="flex justify-end">
                        <button
                            type="button"
                            onClick={() =>
                                onChange(value.filter((_, idx) => idx !== i))
                            }
                            className="text-neutral-400 hover:text-red-500"
                        >
                            <Trash2 className="size-4" />
                        </button>
                    </div>
                    {field.fields?.map((sub) => (
                        <FieldInput
                            key={sub.id}
                            field={sub}
                            value={row[sub.id]}
                            onChange={(v) => setRow(i, sub.id, v)}
                        />
                    ))}
                </div>
            ))}
            <button
                type="button"
                onClick={() => onChange([...value, {}])}
                className="btn-light inline-flex items-center gap-1.5 text-xs"
            >
                <Plus className="size-4" /> Add item
            </button>
        </div>
    );
}

function RelationField({
    field,
    value,
    onChange,
}: {
    field: FieldSchema;
    value: string | number | null;
    onChange: (value: string | number | null) => void;
}) {
    const [options, setOptions] = useState<{ value: string; label: string }[]>(
        [],
    );

    useEffect(() => {
        let active = true;
        fetch(`/admin/builder/options/${field.source}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then((r) => (r.ok ? r.json() : { data: [] }))
            .then((j) => active && setOptions(j.data ?? []))
            .catch(() => {});

        return () => {
            active = false;
        };
    }, [field.source]);

    return (
        <select
            className="form-select"
            value={(value as string) ?? ''}
            onChange={(e) => onChange(e.target.value || null)}
        >
            <option value="">—</option>
            {options.map((o) => (
                <option key={o.value} value={o.value}>
                    {o.label}
                </option>
            ))}
        </select>
    );
}
