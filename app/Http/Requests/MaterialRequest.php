<?php

namespace App\Http\Requests;

use App\Models\Material;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cms') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category_id' => ['nullable', 'integer', 'exists:material_categories,id'],
            'type' => ['required', Rule::in(Material::TYPES)],
            'url' => ['nullable', 'string', 'max:2048'],
            'image_path' => ['nullable', 'string', 'max:2048'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
        ];
    }
}
