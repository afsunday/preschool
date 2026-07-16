<?php

namespace App\Models;

use Illuminate\Support\Facades\File;

/**
 * The class banner library — hand-authored SVGs in `public/images/banners/`.
 *
 * `resources/cms/banners.json` is the single source of truth, shared with
 * resources/js/lib/class-banners.ts, so PHP and the front end cannot drift.
 *
 * Only the key is ever stored on a classroom. Never a colour and never a CSS
 * class — Tailwind cannot scan the database.
 */
final class ClassroomBanner
{
    public const DEFAULT = 'art-table';

    /** @var array<string, mixed>|null */
    protected static ?array $manifest = null;

    /**
     * @return array<string, mixed>
     */
    public static function manifest(): array
    {
        // Cached per process: the file is a build-time asset, not user data.
        return self::$manifest ??= json_decode(
            File::get(resource_path('portal/banners.json')),
            true,
        );
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_column(self::manifest()['banners'], 'key');
    }

    /** @return list<array<string, string>> */
    public static function categories(): array
    {
        return self::manifest()['categories'];
    }

    public static function valid(?string $key): bool
    {
        return $key !== null && in_array($key, self::keys(), true);
    }

    /** Every banner, for tests and seeding. */
    public static function all(): array
    {
        return self::manifest()['banners'];
    }

    /** Reset the process cache (tests only). */
    public static function flush(): void
    {
        self::$manifest = null;
    }
}
