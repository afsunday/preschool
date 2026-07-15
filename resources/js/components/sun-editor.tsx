import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import { Bold, Heading2, ImagePlus, Italic, Link as LinkIcon, List, ListOrdered, Strikethrough, Underline as UnderlineIcon } from 'lucide-react';
import { type ReactNode, useEffect, useRef } from 'react';

type SunEditorProps = {
    value: string;
    onChange: (value: string) => void;
    /** Kept for API compatibility with the previous SunEditor. */
    trackBy?: string;
    /** Kept for API compatibility; ignored by the Tiptap implementation. */
    excludes?: string[];
    /** Kept for API compatibility; ignored by the Tiptap implementation. */
    maxCharCount?: number;
    required?: boolean;
    minHeight?: string;
    placeholder?: string;
    /** Enables the inline-image tool. */
    enableImage?: boolean;
};

/**
 * Rich-text editor built on Tiptap (StarterKit bundles bold/italic/underline/
 * strike/headings/lists/link). Controlled via value/onChange. Content is styled
 * by the `.tiptap` rules in the app stylesheet.
 *
 * This replaces the previous SunEditor implementation but keeps the same export
 * and a compatible prop surface.
 */
export default function SunEditor({ value, onChange, required = false, minHeight = '250px', placeholder = 'Write here…', enableImage = false }: SunEditorProps) {
    const fileInputRef = useRef<HTMLInputElement | null>(null);

    const editor = useEditor({
        immediatelyRender: false,
        extensions: [StarterKit, Placeholder.configure({ placeholder }), ...(enableImage ? [Image.configure({ inline: false })] : [])],
        content: value,
        editorProps: {
            attributes: {
                class: 'tiptap outline-none',
                style: `min-height:${minHeight}`,
            },
        },
        onUpdate: ({ editor }) => onChange(editor.getHTML()),
    });

    // Reflect external value changes (e.g. form reset) without an update loop.
    useEffect(() => {
        if (editor && value !== editor.getHTML()) {
            editor.commands.setContent(value || '', { emitUpdate: false });
        }
    }, [value, editor]);

    if (!editor) return null;

    const promptLink = () => {
        const previous = editor.getAttributes('link').href;
        const url = window.prompt('Link URL', previous || 'https://');
        if (url === null) return;
        if (url === '') {
            editor.chain().focus().extendMarkRange('link').unsetLink().run();
            return;
        }
        editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
    };

    const onPickImage = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        event.target.value = '';
        if (!file) return;

        // Inline the picked file as a data URL. Callers wanting server uploads can
        // wrap this component and manage image insertion themselves.
        const reader = new FileReader();
        reader.onload = () => {
            if (typeof reader.result === 'string') {
                editor.chain().focus().setImage({ src: reader.result }).run();
            }
        };
        reader.readAsDataURL(file);
    };

    return (
        <div className="relative overflow-hidden rounded border border-gray-300 bg-white">
            <div className="flex flex-wrap items-center gap-0.5 border-b border-gray-200 bg-gray-50 p-1.5">
                <ToolBtn active={editor.isActive('bold')} onClick={() => editor.chain().focus().toggleBold().run()}>
                    <Bold size={15} />
                </ToolBtn>
                <ToolBtn active={editor.isActive('italic')} onClick={() => editor.chain().focus().toggleItalic().run()}>
                    <Italic size={15} />
                </ToolBtn>
                <ToolBtn active={editor.isActive('underline')} onClick={() => editor.chain().focus().toggleUnderline().run()}>
                    <UnderlineIcon size={15} />
                </ToolBtn>
                <ToolBtn active={editor.isActive('strike')} onClick={() => editor.chain().focus().toggleStrike().run()}>
                    <Strikethrough size={15} />
                </ToolBtn>
                <span className="mx-1 h-5 w-px bg-zinc-300" />
                <ToolBtn active={editor.isActive('heading', { level: 2 })} onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()}>
                    <Heading2 size={15} />
                </ToolBtn>
                <ToolBtn active={editor.isActive('bulletList')} onClick={() => editor.chain().focus().toggleBulletList().run()}>
                    <List size={15} />
                </ToolBtn>
                <ToolBtn active={editor.isActive('orderedList')} onClick={() => editor.chain().focus().toggleOrderedList().run()}>
                    <ListOrdered size={15} />
                </ToolBtn>
                <ToolBtn active={editor.isActive('link')} onClick={promptLink}>
                    <LinkIcon size={15} />
                </ToolBtn>

                {enableImage && (
                    <>
                        <span className="mx-1 h-5 w-px bg-zinc-300" />
                        <ToolBtn active={false} onClick={() => fileInputRef.current?.click()}>
                            <ImagePlus size={15} />
                        </ToolBtn>
                        <input ref={fileInputRef} type="file" accept="image/*" className="hidden" onChange={onPickImage} />
                    </>
                )}
            </div>

            <EditorContent editor={editor} className="px-3 py-2 text-sm text-gray-900" />

            {/* Hidden mirror so native `required` validation still works inside forms. */}
            <textarea className="!absolute !inset-y-0 !block !h-[1px] !w-full !opacity-0" value={value} onChange={() => {}} required={required} tabIndex={-1} />
        </div>
    );
}

function ToolBtn({ active, onClick, disabled = false, children }: { active: boolean; onClick: () => void; disabled?: boolean; children: ReactNode }) {
    return (
        <button
            type="button"
            onClick={onClick}
            disabled={disabled}
            className={`grid h-7 w-7 place-content-center rounded text-zinc-600 transition-colors hover:bg-zinc-200 disabled:opacity-50 ${active ? 'bg-zinc-900 text-white hover:bg-zinc-900' : ''}`}
        >
            {children}
        </button>
    );
}
