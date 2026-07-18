import { Dialog, DialogBackdrop, DialogPanel } from '@headlessui/react';
import Placeholder from '@tiptap/extension-placeholder';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import {
    Bold,
    Heading2,
    ImagePlus,
    Italic,
    Link as LinkIcon,
    List,
    ListOrdered,
    Strikethrough,
    Underline as UnderlineIcon,
    X,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { ReactNode } from 'react';
import { MediaLibrary } from '@/cms/media';
import type { MediaApi } from '@/cms/media';
import { ResizableImage } from './resizable-image';

type RichTextEditorProps = {
    value: string;
    onChange: (value: string) => void;
    required?: boolean;
    minHeight?: string;
    placeholder?: string;
    /** Enables the inline-image tool. */
    enableImage?: boolean;
    /** When set, the image tool picks from the media library and inserts the
     * item's full URL, instead of inlining a file as a base64 data URL. */
    mediaApi?: MediaApi;
};

/**
 * Rich-text editor built on Tiptap (StarterKit bundles bold/italic/underline/
 * strike/headings/lists/link). Controlled via value/onChange. Content is styled
 * by the `.tiptap` rules in the app stylesheet.
 */
export default function RichTextEditor({
    value,
    onChange,
    required = false,
    minHeight = '250px',
    placeholder = 'Write here…',
    enableImage = false,
    mediaApi,
}: RichTextEditorProps) {
    const fileInputRef = useRef<HTMLInputElement | null>(null);
    const [mediaOpen, setMediaOpen] = useState(false);

    const editor = useEditor({
        immediatelyRender: false,
        extensions: [
            StarterKit,
            Placeholder.configure({ placeholder }),
            ...(enableImage
                ? [ResizableImage.configure({ inline: false })]
                : []),
        ],
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

    if (!editor) {
        return null;
    }

    const promptLink = () => {
        const previous = editor.getAttributes('link').href;
        const url = window.prompt('Link URL', previous || 'https://');

        if (url === null) {
            return;
        }

        if (url === '') {
            editor.chain().focus().extendMarkRange('link').unsetLink().run();

            return;
        }

        editor
            .chain()
            .focus()
            .extendMarkRange('link')
            .setLink({ href: url })
            .run();
    };

    const onPickImage = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        event.target.value = '';

        if (!file) {
            return;
        }

        // Inline the picked file as a data URL. Callers wanting server uploads
        // can wrap this component and manage image insertion themselves.
        const reader = new FileReader();
        reader.onload = () => {
            if (typeof reader.result === 'string') {
                editor.chain().focus().setImage({ src: reader.result }).run();
            }
        };
        reader.readAsDataURL(file);
    };

    return (
        <div className="relative overflow-hidden rounded-[4px] border border-black/10 bg-white">
            <div className="flex flex-wrap items-center gap-0.5 border-b border-black/10 bg-neutral-50 p-1.5">
                <ToolBtn
                    active={editor.isActive('bold')}
                    onClick={() => editor.chain().focus().toggleBold().run()}
                >
                    <Bold size={15} />
                </ToolBtn>
                <ToolBtn
                    active={editor.isActive('italic')}
                    onClick={() => editor.chain().focus().toggleItalic().run()}
                >
                    <Italic size={15} />
                </ToolBtn>
                <ToolBtn
                    active={editor.isActive('underline')}
                    onClick={() =>
                        editor.chain().focus().toggleUnderline().run()
                    }
                >
                    <UnderlineIcon size={15} />
                </ToolBtn>
                <ToolBtn
                    active={editor.isActive('strike')}
                    onClick={() => editor.chain().focus().toggleStrike().run()}
                >
                    <Strikethrough size={15} />
                </ToolBtn>
                <span className="mx-1 h-5 w-px bg-neutral-300" />
                <ToolBtn
                    active={editor.isActive('heading', { level: 2 })}
                    onClick={() =>
                        editor.chain().focus().toggleHeading({ level: 2 }).run()
                    }
                >
                    <Heading2 size={15} />
                </ToolBtn>
                <ToolBtn
                    active={editor.isActive('bulletList')}
                    onClick={() =>
                        editor.chain().focus().toggleBulletList().run()
                    }
                >
                    <List size={15} />
                </ToolBtn>
                <ToolBtn
                    active={editor.isActive('orderedList')}
                    onClick={() =>
                        editor.chain().focus().toggleOrderedList().run()
                    }
                >
                    <ListOrdered size={15} />
                </ToolBtn>
                <ToolBtn active={editor.isActive('link')} onClick={promptLink}>
                    <LinkIcon size={15} />
                </ToolBtn>

                {enableImage && (
                    <>
                        <span className="mx-1 h-5 w-px bg-neutral-300" />
                        <ToolBtn
                            active={false}
                            onClick={() =>
                                mediaApi
                                    ? setMediaOpen(true)
                                    : fileInputRef.current?.click()
                            }
                        >
                            <ImagePlus size={15} />
                        </ToolBtn>
                        {!mediaApi && (
                            <input
                                ref={fileInputRef}
                                type="file"
                                accept="image/*"
                                className="hidden"
                                onChange={onPickImage}
                            />
                        )}
                    </>
                )}
            </div>

            <EditorContent
                editor={editor}
                className="px-3 py-2 text-sm text-gray-900"
            />

            {/* Hidden mirror so native `required` validation still works in forms. */}
            <textarea
                className="!absolute !inset-y-0 !block !h-[1px] !w-full !opacity-0"
                value={value}
                onChange={() => {}}
                required={required}
                tabIndex={-1}
            />

            {mediaApi && (
                <Dialog
                    open={mediaOpen}
                    onClose={() => setMediaOpen(false)}
                    className="relative z-50"
                >
                    <DialogBackdrop className="fixed inset-0 bg-black/50" />
                    <div className="fixed inset-0 flex items-center justify-center p-4">
                        <DialogPanel className="flex h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-[4px] bg-white shadow-xl">
                            <div className="flex items-center justify-between border-b border-black/10 px-4 py-3">
                                <h2 className="text-sm font-semibold">
                                    Insert image
                                </h2>
                                <button
                                    type="button"
                                    onClick={() => setMediaOpen(false)}
                                    className="rounded p-1 text-neutral-400 hover:text-neutral-700"
                                >
                                    <X className="size-4" />
                                </button>
                            </div>
                            <div className="min-h-0 flex-1 p-4">
                                <MediaLibrary
                                    api={mediaApi}
                                    mode="select"
                                    fixedKind="image"
                                    onSelect={(item) => {
                                        // Full URL so the image resolves in an
                                        // emailed newsletter, not just on-site.
                                        const src = item.url.startsWith('http')
                                            ? item.url
                                            : window.location.origin + item.url;
                                        // Default to ~200px tall so a large pick
                                        // isn't huge; resizable/alignable after.
                                        const width =
                                            item.width && item.height
                                                ? Math.min(
                                                      item.width,
                                                      Math.round(
                                                          (200 * item.width) /
                                                              item.height,
                                                      ),
                                                  )
                                                : null;
                                        editor
                                            .chain()
                                            .focus()
                                            .insertContent({
                                                type: 'image',
                                                attrs: {
                                                    src,
                                                    width,
                                                    align: 'center',
                                                },
                                            })
                                            .run();
                                        setMediaOpen(false);
                                    }}
                                />
                            </div>
                        </DialogPanel>
                    </div>
                </Dialog>
            )}
        </div>
    );
}

function ToolBtn({
    active,
    onClick,
    disabled = false,
    children,
}: {
    active: boolean;
    onClick: () => void;
    disabled?: boolean;
    children: ReactNode;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            disabled={disabled}
            className={`grid h-7 w-7 place-content-center rounded-[4px] text-neutral-600 transition-colors hover:bg-neutral-200 disabled:opacity-50 ${active ? 'bg-neutral-900 text-white hover:bg-neutral-900' : ''}`}
        >
            {children}
        </button>
    );
}
