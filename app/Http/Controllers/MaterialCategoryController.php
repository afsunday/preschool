<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialCategoryRequest;
use App\Models\MaterialCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class MaterialCategoryController extends Controller
{
    public function store(MaterialCategoryRequest $request): RedirectResponse
    {
        $name = $request->validated()['name'];

        MaterialCategory::create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'position' => (int) MaterialCategory::max('position') + 1,
        ]);

        return back()->with('success', __('Category added.'));
    }

    public function update(MaterialCategoryRequest $request, MaterialCategory $materialCategory): RedirectResponse
    {
        // Rename only — the slug stays put so existing ?category= links and
        // bookmarks keep working.
        $materialCategory->update(['name' => $request->validated()['name']]);

        return back()->with('success', __('Category renamed.'));
    }

    public function destroy(MaterialCategory $materialCategory): RedirectResponse
    {
        // Materials keep their rows; category_id is nulled by the FK.
        $materialCategory->delete();

        return back()->with('success', __('Category deleted.'));
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $n = 1;

        while (MaterialCategory::where('slug', $slug)->exists()) {
            $slug = $base.'-'.++$n;
        }

        return $slug;
    }
}
