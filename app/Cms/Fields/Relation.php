<?php

namespace App\Cms\Fields;

class Relation extends Field
{
    protected string $type = 'relation';

    protected string $source = '';

    /**
     * The option source key the editor resolves via the builder API's
     * `options` endpoint (e.g. "program_categories"). This is what makes a
     * section *bind* to domain data instead of owning it.
     */
    public function source(string $source): static
    {
        $this->source = $source;

        return $this;
    }

    protected function extra(): array
    {
        return ['source' => $this->source];
    }

    public function sanitize(mixed $value): mixed
    {
        return is_scalar($value) && $value !== '' ? $value : null;
    }
}
