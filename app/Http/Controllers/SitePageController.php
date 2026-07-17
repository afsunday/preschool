<?php

namespace App\Http\Controllers;

use App\Cms\Block;
use App\Http\Requests\StoreContactSubmissionRequest;
use App\Models\ContactSubmission;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SitePageController extends Controller
{
    /** The site chrome: a page with blocks but no route. */
    public const GLOBALS = '_globals';

    public function home(): View
    {
        return $this->render('home', [
            'featuredMaterials' => Material::query()
                ->published()->featured()->ordered()->limit(5)->get(),
        ]);
    }

    public function about(): View
    {
        return $this->render('about');
    }

    public function admissions(): View
    {
        return $this->render('admissions');
    }

    public function resources(Request $request): View
    {
        $category = trim((string) $request->query('category', ''));
        $q = trim((string) $request->query('q', ''));

        return $this->render('resources', [
            'materials' => Material::query()->library($category, $q)->paginate(12)->withQueryString(),
            'categories' => MaterialCategory::query()->ordered()->get(),
            'activeCategory' => $category,
            'q' => $q,
        ]);
    }

    public function gallery(): View
    {
        return $this->render('gallery');
    }

    public function forms(): View
    {
        return $this->render('forms');
    }

    public function faq(): View
    {
        return $this->render('faq');
    }

    public function contact(): View
    {
        return $this->render('contact');
    }

    public function submitContact(StoreContactSubmissionRequest $request): RedirectResponse
    {
        ContactSubmission::create($request->validated());

        return back()->with('contactSuccess', __('Thanks! Your message has been sent.'));
    }

    /**
     * The chrome every page shares — navbar, newsletter, footer.
     *
     * @return Collection<string, Block>
     */
    public static function globals(): Collection
    {
        $page = Page::where('slug', self::GLOBALS)->first();

        return $page === null ? collect() : self::blocks($page)->keyBy('type');
    }
}
