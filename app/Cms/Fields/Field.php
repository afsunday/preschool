<?php

namespace App\Cms\Fields;

use Illuminate\Support\Str;
use JsonSerializable;

/**
 * A single editable field in a section's schema. Pure data — serialises to the
 * `FieldSchema` JSON the React field panel renders. No Blade, no HTML here.
 */
abstract class Field implements JsonSerializable
{
    protected string $type;

    protected mixed $default = null;

    protected bool $required = false;

    public function __construct(
        public readonly string $id,
        public ?string $label = null,
    ) {
        $this->label ??= Str::headline($id);
    }

    public static function make(string $id, ?string $label = null): static
    {
        return new static($id, $label);
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    public function required(bool $value = true): static
    {
        $this->required = $value;

        return $this;
    }

    /**
     * Coerce/validate a stored value to this field's type. Overridden per type;
     * the base treats empty as null and passes scalars through. This is the
     * enforcement that stops arbitrary junk landing in a section's settings.
     */
    public function sanitize(mixed $value): mixed
    {
        return $value === '' ? null : $value;
    }

    /**
     * Per-type extra keys (options, source, fields, …). Overridden by subclasses.
     *
     * @return array<string, mixed>
     */
    protected function extra(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'id' => $this->id,
            'type' => $this->type,
            'label' => $this->label,
            'default' => $this->default,
            'required' => $this->required ?: null,
            ...$this->extra(),
        ], fn ($v) => $v !== null);
    }
}
