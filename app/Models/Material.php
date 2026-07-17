<?php

namespace App\Models;

use Database\Factories\MaterialFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    /**
     * @use HasFactory<MaterialFactory>
     */
    use HasFactory;

    public const TYPES = ['download', 'video', 'article'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'title',
        'description',
        'type',
        'url',
        'image_path',
        'is_featured',
        'position',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<MaterialCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MaterialCategory::class, 'category_id');
    }

    public function ctaLabel(): string
    {
        return match ($this->type) {
            'download' => 'Download File',
            'video' => 'Watch Video',
            default => 'Read',
        };
    }

    public function ctaIcon(): ?string
    {
        return match ($this->type) {
            'download' => 'lucide-download',
            'video' => 'lucide-play',
            default => null,
        };
    }

    /**
     * @param  Builder<Material>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<Material>  $query
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * @param  Builder<Material>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * @param  Builder<Material>  $query
     */
    public function scopeForCategory(Builder $query, ?string $slug): void
    {
        $slug = trim((string) $slug);

        if ($slug === '' || $slug === 'all') {
            return;
        }

        $query->whereHas('category', fn (Builder $q) => $q->where('slug', $slug));
    }

    /**
     * @param  Builder<Material>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->latest('published_at');
    }

    /**
     * The public resources listing: published, filtered by category and search,
     * in display order.
     *
     * @param  Builder<Material>  $query
     */
    public function scopeLibrary(Builder $query, ?string $category = null, ?string $search = null): void
    {
        $query->published()->forCategory($category)->search($search)->ordered();
    }
}
