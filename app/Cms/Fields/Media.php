<?php

namespace App\Cms\Fields;

class Media extends Field
{
    protected string $type = 'media';

    protected ?string $kind = null;

    protected bool $multiple = false;

    /**
     * Restrict the picker to one media kind (e.g. "image").
     */
    public function kind(string $kind): static
    {
        $this->kind = $kind;

        return $this;
    }

    public function images(): static
    {
        return $this->kind('image');
    }

    public function multiple(bool $value = true): static
    {
        $this->multiple = $value;

        return $this;
    }

    protected function extra(): array
    {
        return [
            'kind' => $this->kind,
            'multiple' => $this->multiple ?: null,
        ];
    }

    public function sanitize(mixed $value): mixed
    {
        if ($this->multiple) {
            return collect(is_array($value) ? $value : [])
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        return is_numeric($value) ? (int) $value : null;
    }
}
