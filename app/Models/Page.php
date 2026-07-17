<?php

namespace App\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $status
 * @property Carbon|null $published_at
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property int|null $og_media_id
 * @property string|null $header_scripts
 * @property string|null $footer_scripts
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'slug', 'title', 'status', 'published_at',
    'meta_title', 'meta_description', 'og_media_id',
    'header_scripts', 'footer_scripts',
])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    /**
     * Top-level blocks (nesting is via each block's children()).
     *
     * @return HasMany<PageBlock, $this>
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)
            ->whereNull('parent_id')
            ->orderBy('position');
    }

    /**
     * @return HasMany<PageBlock, $this>
     */
    public function allBlocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('position');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }

    /**
     * @return HasMany<PageRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest();
    }
}
