<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialRequest;
use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MaterialController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('materials/index', [
            'materials' => Material::query()
                ->with('category')
                ->orderBy('position')
                ->latest('id')
                ->get()
                ->map($this->present(...)),
            'categories' => MaterialCategory::query()->ordered()->get()
                ->map(fn (MaterialCategory $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'materialsCount' => $c->materials()->count(),
                ]),
            'types' => Material::TYPES,
        ]);
    }

    public function store(MaterialRequest $request): RedirectResponse
    {
        $material = new Material;
        $this->fill($material, $request);

        return back()->with('success', __('Material created.'));
    }

    public function update(MaterialRequest $request, Material $material): RedirectResponse
    {
        $this->fill($material, $request);

        return back()->with('success', __('Material updated.'));
    }

    public function destroy(Material $material): RedirectResponse
    {
        $material->delete();

        return back()->with('success', __('Material deleted.'));
    }

    protected function fill(Material $material, MaterialRequest $request): void
    {
        $material->fill($request->safe()->except('is_published'));

        // Keep the original publish date when it was already live; stamp now on
        // first publish; clear it to unpublish.
        $material->published_at = $request->boolean('is_published')
            ? ($material->published_at ?? now())
            : null;

        $material->save();
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(Material $material): array
    {
        return [
            'id' => $material->id,
            'title' => $material->title,
            'description' => $material->description,
            'categoryId' => $material->category_id,
            'category' => $material->category?->name,
            'type' => $material->type,
            'url' => $material->url,
            'imagePath' => $material->image_path,
            'isFeatured' => $material->is_featured,
            'isPublished' => $material->published_at !== null,
        ];
    }
}
