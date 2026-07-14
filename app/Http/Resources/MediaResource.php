<?php

namespace App\Http\Resources;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url(),
            'kind' => $this->kind,
            'mimeType' => $this->mime_type,
            'originalName' => $this->original_name,
            'title' => $this->title,
            'alt' => $this->alt,
            'description' => $this->description,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
