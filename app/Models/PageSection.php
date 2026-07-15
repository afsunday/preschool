<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $page_id
 * @property int|null $parent_id
 * @property string $type
 * @property string|null $name
 * @property int $position
 * @property bool $is_visible
 * @property array<string, mixed>|null $settings
 * @property int $schema_version
 */
#[Fillable(['page_id', 'parent_id', 'type', 'name', 'position', 'is_visible', 'settings', 'schema_version'])]
class PageSection extends Model
{
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * @return HasMany<PageSection, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }
}
