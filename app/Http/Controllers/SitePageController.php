<?php

namespace App\Http\Controllers;

use App\Cms\Block;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class SitePageController extends Controller
{
    /**
     * Render a public page: its Blade view (named by slug), fed the page's
     * blocks as data. The view loops the blocks and renders them inline.
     */
    public function show(string $slug = 'home'): View
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        return view($slug, [
            'page' => $page,
            'blocks' => $this->blocks($page),
            'editor' => false,
        ]);
    }

    /**
     * The page's visible top-level blocks as Block objects.
     *
     * @return Collection<int, Block>
     */
    public static function blocks(Page $page): Collection
    {
        return $page->allSections()->get()
            ->whereNull('parent_id')
            ->where('is_visible', true)
            ->sortBy('position')
            ->map(fn (PageSection $s) => new Block($s->id, $s->type, $s->name, $s->settings ?? []))
            ->values();
    }
}
