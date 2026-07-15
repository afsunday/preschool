<?php

namespace App\Http\Controllers;

use App\Cms\PageRenderer;
use App\Models\Page;
use Illuminate\Contracts\View\View;

class SitePageController extends Controller
{
    public function __construct(protected PageRenderer $renderer) {}

    /**
     * Render a public page from the DB via the shared PageRenderer, yielding an
     * addressable SectionCollection (`$sections->section('hero')`).
     */
    public function show(string $slug = 'home'): View
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        $sections = $this->renderer->render($page);

        return view('site.page', compact('page', 'sections'));
    }
}
