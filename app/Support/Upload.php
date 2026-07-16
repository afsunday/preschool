<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Ordinary uploads — a class photo, a child's picture, an image in a chat.
 *
 * Deliberately separate from the media library: that is for the public site's
 * CMS, where assets are reusable, need alt text, and are managed by hand. A
 * teacher posting twenty photos of a painting session wants none of that, and
 * would bury the asset list if they got it.
 *
 * Two steps, so the wait happens while the user is still typing:
 *
 *   1. `temp()`  — the file lands the moment it's chosen (AJAX), under `temp/`.
 *   2. `keep()`  — on submit, it moves to its permanent home.
 *
 * Anything left in `temp/` was abandoned and is safe to sweep.
 */
class Upload
{
    /** Portal photos are served directly for speed; names are unguessable. */
    public const DISK = 'public';

    public const TEMP = 'temp';

    /**
     * Store a just-chosen file in the temporary area.
     *
     * @return array{path: string, url: string, name: string, size: int}
     */
    public static function temp(UploadedFile $file): array
    {
        $path = $file->storeAs(self::TEMP, self::name($file), ['disk' => self::DISK]);

        return [
            'path' => $path,
            'url' => self::url($path),
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Move a temporary file to where it lives for good.
     *
     * Idempotent: a path already in `$directory` is returned untouched, so an
     * edit that re-submits an existing photo is a no-op rather than a failure.
     */
    public static function keep(string $path, string $directory): string
    {
        $disk = Storage::disk(self::DISK);
        $permanent = trim($directory, '/').'/'.basename($path);

        if ($path === $permanent) {
            return $permanent;
        }

        // Only a file we put in temp/ may be promoted — otherwise a crafted
        // path could move something else on the disk.
        if (! Str::startsWith($path, self::TEMP.'/') || ! $disk->exists($path)) {
            abort(422, 'That upload has expired. Please choose the file again.');
        }

        if (! $disk->exists($permanent)) {
            abort_unless($disk->move($path, $permanent), 500, 'Could not store the upload.');
        }

        return $permanent;
    }

    /**
     * Promote several at once, keeping order.
     *
     * @param  list<string>  $paths
     * @return list<string>
     */
    public static function keepAll(array $paths, string $directory): array
    {
        return array_values(array_map(fn (string $p) => self::keep($p, $directory), $paths));
    }

    public static function remove(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /** @param list<string>|null $paths */
    public static function removeAll(?array $paths): void
    {
        foreach ($paths ?? [] as $path) {
            self::remove($path);
        }
    }

    public static function url(?string $path): ?string
    {
        return $path === null || $path === ''
            ? null
            : Storage::disk(self::DISK)->url($path);
    }

    /** Random name: the original is kept in the DB, never on disk. */
    protected static function name(UploadedFile $file): string
    {
        return Str::uuid().'.'.($file->getClientOriginalExtension() ?: 'bin');
    }
}
