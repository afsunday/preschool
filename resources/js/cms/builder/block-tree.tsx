import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import { Eye, EyeOff, GripVertical, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { cn } from '../lib/cn';
import type { PageBlock, BlockType } from './types';

export function BlockTree({
    blocks,
    schemas,
    onSelect,
    onAdd,
    onRemove,
    onToggleVisible,
    onReorder,
}: {
    blocks: PageBlock[];
    schemas: BlockType[];
    selectedId: number | null;
    onSelect: (id: number) => void;
    onAdd: (type: string) => void;
    onRemove: (id: number) => void;
    onToggleVisible: (id: number) => void;
    onReorder: (from: number, to: number) => void;
}) {
    const [dragIndex, setDragIndex] = useState<number | null>(null);
    const [overIndex, setOverIndex] = useState<number | null>(null);

    const nameFor = (type: string) =>
        schemas.find((s) => s.key === type)?.name ?? type;

    // Group schemas for the Add menu.
    const groups = schemas.reduce<Record<string, BlockType[]>>((acc, s) => {
        (acc[s.group] ??= []).push(s);

        return acc;
    }, {});

    return (
        <div className="flex h-full flex-col">
            <div className="flex items-center justify-between px-3 pt-3 pb-2">
                <span className="text-[13px] font-semibold text-neutral-800">
                    Blocks
                    <span className="ml-1.5 text-xs font-normal text-neutral-400">
                        {blocks.length}
                    </span>
                </span>
                <Menu as="div" className="relative">
                    <MenuButton className="inline-flex items-center gap-1 rounded-[4px] bg-neutral-900 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-neutral-800">
                        <Plus className="size-3.5" /> Add block
                    </MenuButton>
                    <MenuItems
                        anchor="bottom end"
                        className="z-50 mt-1 max-h-80 w-56 overflow-y-auto rounded-[6px] border border-black/10 bg-white py-1 shadow-lg focus:outline-none"
                    >
                        {Object.entries(groups).map(([group, items]) => (
                            <div key={group}>
                                <p className="px-3 pt-2 pb-1 text-[10px] font-semibold tracking-wide text-neutral-400 uppercase">
                                    {group}
                                </p>
                                {items.map((s) => (
                                    <MenuItem key={s.key}>
                                        <button
                                            type="button"
                                            onClick={() => onAdd(s.key)}
                                            className="block w-full px-3 py-1.5 text-left text-sm text-neutral-700 data-focus:bg-neutral-100"
                                        >
                                            {s.name}
                                        </button>
                                    </MenuItem>
                                ))}
                            </div>
                        ))}
                    </MenuItems>
                </Menu>
            </div>

            <div className="flex-1 overflow-y-auto px-2 pb-2">
                {blocks.length === 0 && (
                    <div className="mt-6 rounded-[6px] border border-dashed border-black/10 px-3 py-8 text-center">
                        <p className="text-sm text-neutral-400">
                            No blocks yet.
                        </p>
                        <p className="mt-0.5 text-xs text-neutral-400">
                            Use “Add block” to start.
                        </p>
                    </div>
                )}

                {blocks.map((block, index) => (
                    <div
                        key={block.id}
                        draggable
                        onDragStart={() => setDragIndex(index)}
                        onDragEnd={() => {
                            setDragIndex(null);
                            setOverIndex(null);
                        }}
                        onDragOver={(e) => {
                            e.preventDefault();

                            if (overIndex !== index) {
                                setOverIndex(index);
                            }
                        }}
                        onDrop={() => {
                            if (dragIndex !== null && dragIndex !== index) {
                                onReorder(dragIndex, index);
                            }

                            setDragIndex(null);
                            setOverIndex(null);
                        }}
                        onClick={() => onSelect(block.id)}
                        className={cn(
                            'group relative mb-1.5 flex items-center gap-2 rounded-[2px] border bg-white px-2 py-2.5 transition hover:bg-neutral-50',
                            overIndex === index && dragIndex !== index
                                ? 'border-neutral-900'
                                : 'border-neutral-300',
                            dragIndex === index && 'opacity-40',
                            !block.isVisible && 'opacity-50',
                        )}
                    >
                        <GripVertical className="size-4 shrink-0 cursor-grab text-neutral-300 group-hover:text-neutral-400" />

                        <span className="grid size-7 shrink-0 place-items-center rounded-[2px] bg-neutral-100 text-xs font-semibold text-neutral-500">
                            {index + 1}
                        </span>

                        <span className="min-w-0 flex-1 truncate text-sm font-medium text-neutral-800">
                            {nameFor(block.type)}
                        </span>

                        <div className="flex shrink-0 items-center gap-0.5 opacity-0 transition group-hover:opacity-100">
                            <button
                                type="button"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    onToggleVisible(block.id);
                                }}
                                title={block.isVisible ? 'Hide' : 'Show'}
                                className="grid size-6 place-items-center rounded-[4px] text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700"
                            >
                                {block.isVisible ? (
                                    <Eye className="size-4" />
                                ) : (
                                    <EyeOff className="size-4" />
                                )}
                            </button>
                            <button
                                type="button"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    onRemove(block.id);
                                }}
                                title="Delete"
                                className="grid size-6 place-items-center rounded-[4px] text-neutral-400 hover:bg-red-50 hover:text-red-500"
                            >
                                <Trash2 className="size-4" />
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
