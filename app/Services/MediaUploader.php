<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaUploader
{
    /**
     * The disk media is stored on.
     */
    protected string $disk = 'public';

    /**
     * The directory (within the disk) files are written to.
     */
    protected string $directory = 'media';

    /**
     * Store an uploaded file and create its `media` row.
     *
     * No hashing, no conversions — move the file, probe dimensions, write the row.
     */
    public function store(UploadedFile $file, ?int $userId = null): Media
    {
        $original = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $filename = Str::uuid()->toString().($extension !== '' ? '.'.$extension : '');

        $path = $file->storeAs($this->directory, $filename, ['disk' => $this->disk]);

        [$width, $height] = $this->dimensions($file);

        return Media::create([
            'disk' => $this->disk,
            'path' => $path,
            'filename' => $filename,
            'original_name' => $original,
            'extension' => $extension ?: null,
            'mime_type' => $file->getClientMimeType(),
            'kind' => $this->kindFor($file->getClientMimeType(), $extension),
            'size' => $file->getSize() ?: 0,
            'width' => $width,
            'height' => $height,
            'title' => pathinfo($original, PATHINFO_FILENAME),
            'uploaded_by' => $userId,
        ]);
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    protected function dimensions(UploadedFile $file): array
    {
        if (! str_starts_with((string) $file->getClientMimeType(), 'image/')) {
            return [null, null];
        }

        $info = @getimagesize($file->getRealPath());

        return $info === false ? [null, null] : [$info[0], $info[1]];
    }

    /**
     * Map a mime type / extension to a coarse kind bucket.
     */
    public function kindFor(?string $mime, ?string $extension): string
    {
        $mime = (string) $mime;

        return match (true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            str_starts_with($mime, 'audio/') => 'audio',
            $mime === 'application/pdf',
            in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx', 'txt'], true) => 'document',
            in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'], true) => 'archive',
            default => 'other',
        };
    }
}
