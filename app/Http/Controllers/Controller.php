<?php

namespace App\Http\Controllers;

use App\Cms\Block;
use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Load a page by slug and render its Blade view (named by slug), fed the
     * page's blocks. Callers pass whatever extra data that page needs.
     *
     * @param  array<string, mixed>  $extra
     */
    protected function render(string $slug, array $extra = []): View
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        return view($slug, [
            'page' => $page,
            'blocks' => self::blocks($page),
            'editor' => false,
            ...$extra,
        ]);
    }

    /**
     * A page's visible top-level blocks as Block objects.
     *
     * @return Collection<int, Block>
     */
    public static function blocks(Page $page): Collection
    {
        return $page->allBlocks()->get()
            ->whereNull('parent_id')
            ->where('is_visible', true)
            ->sortBy('position')
            ->map(fn (PageBlock $b) => new Block($b->id, $b->type, $b->name, $b->settings ?? []))
            ->values();
    }
}
