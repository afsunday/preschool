/**
 * The page-builder contract. Any backend serving these shapes drives the
 * editor; the React side never assumes Blade.
 */

export interface FieldSchema {
    id: string;
    type:
        | 'text'
        | 'textarea'
        | 'richtext'
        | 'number'
        | 'url'
        | 'color'
        | 'select'
        | 'media'
        | 'repeater'
        | 'relation';
    label: string;
    default?: unknown;
    required?: boolean;
    options?: { value: string; label: string }[];
    kind?: string;
    multiple?: boolean;
    fields?: FieldSchema[];
    source?: string;
}

export interface BlockType {
    key: string;
    name: string;
    group: string;
    version: number;
    acceptsChildren: boolean;
    fields: FieldSchema[];
}

export interface PageBlock {
    id: number;
    type: string;
    /** Blueprint identity. Null for blocks added here — sync never touches those. */
    key?: string | null;
    name?: string | null;
    position: number;
    isVisible: boolean;
    schemaVersion?: number;
    settings: Record<string, unknown>;
    children?: PageBlock[];
}

export interface PageDoc {
    id: number;
    slug: string;
    title: string;
    status: 'draft' | 'published';
    meta: {
        title?: string | null;
        description?: string | null;
        ogMediaId?: number | null;
    };
    headerScripts?: string | null;
    footerScripts?: string | null;
    blocks: PageBlock[];
}

export interface BuilderApi {
    /** The block types this page may use: its own + the globals'. */
    schema(pageId: number): Promise<BlockType[]>;
    getPage(id: number): Promise<PageDoc>;
    savePage(id: number, doc: PageDoc): Promise<PageDoc>;
    /** Render the whole page from the current (unsaved) doc → full HTML. */
    renderPage(id: number, doc: PageDoc): Promise<string>;
    options(source: string): Promise<{ value: string; label: string }[]>;
}
