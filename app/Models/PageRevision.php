<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $page_id
 * @property int|null $user_id
 * @property array<string, mixed> $snapshot
 */
#[Fillable(['page_id', 'user_id', 'snapshot'])]
class PageRevision extends Model
{
    protected function casts(): array
    {
        return ['snapshot' => 'array'];
    }

    /**
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
