import { ArrowLeft } from 'lucide-react';
import { FieldInput } from './field-input';
import type { PageBlock, BlockType } from './types';

export function FieldPanel({
    block,
    schema,
    onChange,
    onBack,
}: {
    block: PageBlock;
    schema: BlockType;
    onChange: (key: string, value: unknown) => void;
    onBack: () => void;
}) {
    return (
        <div className="flex h-full flex-col">
            <div className="flex items-center gap-1.5 border-b border-black/10 px-2 py-2">
                <button
                    type="button"
                    onClick={onBack}
                    className="grid size-7 place-items-center rounded-[6px] text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900"
                >
                    <ArrowLeft className="size-4" />
                </button>
                <h2 className="text-sm font-semibold">{schema.name}</h2>
            </div>
            <div className="flex-1 space-y-4 overflow-y-auto p-3">
                {schema.fields.map((field) => (
                    <FieldInput
                        key={field.id}
                        field={field}
                        value={block.settings[field.id]}
                        onChange={(v) => onChange(field.id, v)}
                    />
                ))}
            </div>
        </div>
    );
}
