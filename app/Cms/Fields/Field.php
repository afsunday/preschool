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
     * Reconcile this field on pull: decide the value a block should hold given
     * what it already stores and what the blueprint seeds.
     *
     * Value-preserving — an edit always wins:
     *   - already stored  -> keep it (the builder owns content)
     *   - only in seed     -> adopt it (a brand-new field, filled from the design)
     *   - in neither       -> absent (nothing invents a default; the view falls
     *                         back at render via get($key, …))
     *
     * Returns [present, value]; present=false means leave the key out entirely,
     * so a field the schema dropped is pruned by simply never being asked.
     *
     * @param  array<string, mixed>  $stored  the block's current settings
     * @param  array<string, mixed>  $seed  the blueprint block's settings
     * @return array{0: bool, 1: mixed}
     */
    public function reconcile(array $stored, array $seed): array
    {
        if (array_key_exists($this->id, $stored)) {
            return [true, $this->sanitize($stored[$this->id])];
        }

        if (array_key_exists($this->id, $seed)) {
            return [true, $this->sanitize($seed[$this->id])];
        }

        return [false, null];
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
