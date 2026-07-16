import manifest from '../../portal/banners.json';

/**
 * The class banner library — hand-authored SVGs in `public/images/banners/`.
 *
 * `resources/cms/banners.json` is the single source of truth, read by this file
 * and by App\Models\ClassroomBanner, so the two cannot drift.
 *
 * The DB stores only the key ("art-table"). Tailwind cannot scan the database,
 * so nothing here becomes a class name — the cover is an inline background-image.
 */

export interface BannerCategory {
    key: string;
    label: string;
}

export interface Banner {
    key: string;
    label: string;
    category: string;
    /** The banner's flat background colour, handy for placeholders. */
    bg: string;
}

export const BANNER_CATEGORIES: BannerCategory[] = manifest.categories;
export const BANNERS: Banner[] = manifest.banners;

export const DEFAULT_BANNER = 'art-table';

export function findBanner(key: string | null | undefined): Banner | null {
    if (!key) {
        return null;
    }

    return BANNERS.find((b) => b.key === key) ?? null;
}

export function bannerUrl(key: string | null | undefined): string {
    return `/images/banners/${findBanner(key)?.key ?? DEFAULT_BANNER}.svg`;
}

/**
 * Inline cover style. The artwork is composed with its objects on the right and
 * the left third deliberately empty, so it is anchored right and the class title
 * always lands on clear colour.
 */
export function bannerStyle(
    key: string | null | undefined,
): React.CSSProperties {
    const banner = findBanner(key) ?? findBanner(DEFAULT_BANNER);

    return {
        backgroundColor: banner?.bg ?? '#2d2b3a',
        backgroundImage: `url("${bannerUrl(key)}")`,
        backgroundSize: 'cover',
        backgroundPosition: 'center right',
        backgroundRepeat: 'no-repeat',
    };
}
