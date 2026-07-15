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

export interface SectionSchema {
    key: string;
    name: string;
    group: string;
    version: number;
    acceptsChildren: boolean;
    fields: FieldSchema[];
}

export interface SectionInstance {
    id: number;
    type: string;
    name?: string | null;
    position: number;
    isVisible: boolean;
    schemaVersion?: number;
    settings: Record<string, unknown>;
    children?: SectionInstance[];
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
    sections: SectionInstance[];
}

export interface BuilderApi {
    schema(): Promise<SectionSchema[]>;
    getPage(id: number): Promise<PageDoc>;
    savePage(id: number, doc: PageDoc): Promise<PageDoc>;
    renderSection(
        type: string,
        settings: Record<string, unknown>,
    ): Promise<string>;
    options(source: string): Promise<{ value: string; label: string }[]>;
}
