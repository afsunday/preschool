/**
 * The portable contract for the media library. Anything that satisfies
 * `MediaApi` can drive the UI — copy this folder into another project, write a
 * new adapter, done. Nothing here is Laravel- or Inertia-specific.
 */

export type MediaKind =
    'image' | 'video' | 'audio' | 'document' | 'archive' | 'other';

export interface MediaItem {
    id: number;
    url: string; // the original file — no conversions/thumbnails
    kind: MediaKind;
    mimeType: string | null;
    originalName: string;
    title: string | null; // searchable
    alt: string | null; // searchable + used as the <img alt> on the site
    description: string | null; // searchable
    size: number;
    width: number | null;
    height: number | null;
    createdAt: string | null;
}

export interface MediaQuery {
    q?: string;
    kind?: MediaKind | 'all';
    cursor?: string | null;
}

export interface MediaListResult {
    data: MediaItem[];
    nextCursor: string | null;
}

export type MediaPatch = Partial<
    Pick<MediaItem, 'title' | 'alt' | 'description'>
>;

/** A place this media item is currently attached to. */
export interface MediaUsage {
    type: string;
    label: string;
}

/** Thrown by `destroy` when the file is still attached somewhere. */
export class MediaInUseError extends Error {
    constructor(public usages: MediaUsage[]) {
        super('This file is still in use.');
        this.name = 'MediaInUseError';
    }
}

export interface UploadOptions {
    onProgress?: (percent: number) => void;
}

export interface MediaApi {
    list(query: MediaQuery): Promise<MediaListResult>;
    upload(files: File[], opts?: UploadOptions): Promise<MediaItem[]>;
    update(id: number, patch: MediaPatch): Promise<MediaItem>;
    destroy(id: number): Promise<void>;
}
