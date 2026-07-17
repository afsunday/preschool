<?php

namespace App\Http\Controllers;

use App\Cms\Block;
use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class SitePageController extends Controller
{
    /** The site chrome: a page with blocks but no route. */
    public const GLOBALS = '_globals';

    /**
     * Render a public page: its Blade view (named by slug), fed the page's
     * blocks as data. The view loops the blocks and renders them inline.
     *
     * The layout does the same with the site's global blocks, so there is one
     * model throughout: a thing has blocks, a view renders them.
     */
    public function show(string $slug = 'home'): View
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        return view($slug, [
            'page' => $page,
            'blocks' => self::blocks($page),
            'editor' => false,
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

    /**
     * The chrome every page shares — navbar, newsletter, footer.
     *
     * Edited once and rendered by the layout, so a page view never places it and
     * the eight copies can't drift apart.
     *
     * Keyed by block type, not ordered: there is one navbar, one footer, and the
     * layout decides where each sits — so `$globals['site_navbar']` beats looping
     * the collection to find it. (A page's own blocks stay ordered; only the
     * chrome is a lookup.)
     *
     * @return Collection<string, Block>
     */
    public static function globals(): Collection
    {
        $page = Page::where('slug', self::GLOBALS)->first();

        return $page === null ? collect() : self::blocks($page)->keyBy('type');
    }
}
