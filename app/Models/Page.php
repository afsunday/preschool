<?php

namespace App\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'status',
        'is_system',
        'published_at',
        'meta_title',
        'meta_description',
        'og_image',
        'header_scripts',
        'footer_scripts',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'is_system' => 'boolean'];
    }

    /**
     * The social-share image as an absolute URL — og:image needs one.
     */
    public function ogImageUrl(): ?string
    {
        if (blank($this->og_image)) {
            return null;
        }

        return Str::startsWith($this->og_image, ['http://', 'https://'])
            ? $this->og_image
            : url($this->og_image);
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
     * @return HasMany<PageRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest();
    }
}
