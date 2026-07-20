<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One block on a page: a placement of a block type, with that type's field
 * values in `settings`.
 *
 * `key` is the blueprint's stable identity — null for blocks added in the
 * editor, which `pull` must never touch.
 *
 * @property int $id
 * @property int $page_id
 * @property int|null $parent_id
 * @property string $type
 * @property string|null $key
 * @property string|null $name
 * @property int $position
 * @property bool $is_visible
 * @property array<string, mixed>|null $settings
 * @property int $schema_version
 */
class PageBlock extends Model
{
    protected $fillable = ['page_id', 'parent_id', 'type', 'key', 'name', 'position', 'is_visible', 'settings', 'schema_version'];

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
     * @return HasMany<PageBlock, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }
}
