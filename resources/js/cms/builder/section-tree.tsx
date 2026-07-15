import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import {
    ChevronRight,
    Eye,
    EyeOff,
    GripVertical,
    Plus,
    Trash2,
} from 'lucide-react';
import { useState } from 'react';
import { cn } from '../lib/cn';
import { SectionInstance, SectionSchema } from './types';

export function SectionTree({
    sections,
    schemas,
    selectedId,
    onSelect,
    onAdd,
    onRemove,
    onToggleVisible,
    onReorder,
}: {
    sections: SectionInstance[];
    schemas: SectionSchema[];
    selectedId: number | null;
    onSelect: (id: number) => void;
    onAdd: (type: string) => void;
    onRemove: (id: number) => void;
    onToggleVisible: (id: number) => void;
    onReorder: (from: number, to: number) => void;
}) {
    const [dragIndex, setDragIndex] = useState<number | null>(null);
    const nameFor = (type: string) =>
        schemas.find((s) => s.key === type)?.name ?? type;

    return (
        <div className="flex h-full flex-col">
            <div className="flex items-center justify-between border-b border-black/10 px-3 py-2">
                <span className="text-xs font-semibold tracking-wide text-neutral-500 uppercase">
                    Sections
                </span>
                <Menu as="div" className="relative">
                    <MenuButton className="inline-flex items-center gap-1 rounded-[4px] bg-neutral-900 px-2 py-1 text-xs font-medium text-white hover:bg-neutral-800">
                        <Plus className="size-3.5" /> Add
                    </MenuButton>
                    <MenuItems
                        anchor="bottom start"
                        className="z-50 mt-1 w-52 rounded-[4px] border border-black/10 bg-white py-1 shadow-lg focus:outline-none"
                    >
                        {schemas.map((s) => (
                            <MenuItem key={s.key}>
                                <button
                                    type="button"
                                    onClick={() => onAdd(s.key)}
                                    className="block w-full px-3 py-1.5 text-left text-sm data-focus:bg-neutral-100"
                                >
                                    {s.name}
                                    <span className="ml-1 text-xs text-neutral-400">
                                        {s.group}
                                    </span>
                                </button>
                            </MenuItem>
                        ))}
                    </MenuItems>
                </Menu>
            </div>

            <div className="flex-1 overflow-y-auto p-2">
                {sections.length === 0 && (
                    <p className="px-2 py-6 text-center text-sm text-neutral-400">
                        No sections yet. Use “Add”.
                    </p>
                )}
                {sections.map((section, index) => (
                    <div
                        key={section.id}
                        draggable
                        onDragStart={() => setDragIndex(index)}
                        onDragOver={(e) => e.preventDefault()}
                        onDrop={() => {
                            if (dragIndex !== null && dragIndex !== index) {
                                onReorder(dragIndex, index);
                            }
                            setDragIndex(null);
                        }}
                        onClick={() => onSelect(section.id)}
                        className={cn(
                            'group mb-1 flex items-center gap-1.5 rounded-[6px] border border-transparent px-2 py-2.5 text-sm transition hover:border-black/10 hover:bg-neutral-50',
                            !section.isVisible && 'opacity-50',
                        )}
                    >
                        <GripVertical className="size-4 shrink-0 cursor-grab text-neutral-300 group-hover:text-neutral-400" />
                        <span className="flex-1 truncate font-medium text-neutral-700">
                            {nameFor(section.type)}
                        </span>
                        <button
                            type="button"
                            onClick={(e) => {
                                e.stopPropagation();
                                onToggleVisible(section.id);
                            }}
                            className="shrink-0 text-neutral-400 opacity-0 transition group-hover:opacity-100 hover:text-neutral-700"
                        >
                            {section.isVisible ? (
                                <Eye className="size-4" />
                            ) : (
                                <EyeOff className="size-4" />
                            )}
                        </button>
                        <button
                            type="button"
                            onClick={(e) => {
                                e.stopPropagation();
                                onRemove(section.id);
                            }}
                            className="shrink-0 text-neutral-400 opacity-0 transition group-hover:opacity-100 hover:text-red-500"
                        >
                            <Trash2 className="size-4" />
                        </button>
                        <ChevronRight className="size-4 shrink-0 text-neutral-300" />
                    </div>
                ))}
            </div>
        </div>
    );
}
