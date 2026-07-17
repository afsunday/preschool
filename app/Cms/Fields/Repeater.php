<?php

namespace App\Cms\Fields;

class Repeater extends Field
{
    protected string $type = 'repeater';

    /** @var array<int, Field> */
    protected array $fields = [];

    /**
     * @param  array<int, Field>  $fields
     */
    public function fields(array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    protected function extra(): array
    {
        return [
            'fields' => array_map(fn (Field $f) => $f->jsonSerialize(), $this->fields),
        ];
    }

    public function sanitize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_map($this->reshapeRow(...), $value));
    }

    /**
     * Reconcile a repeater on pull.
     *
     * The block's own rows win — count, order and values are the builder's, so a
     * schema change can't add, drop or reorder a row. A repeater the block hasn't
     * got yet is seeded whole from the blueprint. Either way each row is reshaped
     * against the current sub-fields: a removed sub-key is pruned, a new sub-key
     * is left absent (rows have no stable identity to seed one from — you fill it
     * in the builder). See {@see Field::reconcile()}.
     *
     * @param  array<string, mixed>  $stored
     * @param  array<string, mixed>  $seed
     * @return array{0: bool, 1: mixed}
     */
    public function reconcile(array $stored, array $seed): array
    {
        $rows = match (true) {
            array_key_exists($this->id, $stored) => $stored[$this->id],
            array_key_exists($this->id, $seed) => $seed[$this->id],
            default => null,
        };

        if (! is_array($rows)) {
            // Absent from both -> stay absent; present-but-not-an-array -> [].
            return $rows === null ? [false, null] : [true, []];
        }

        return [true, array_values(array_map($this->reshapeRow(...), $rows))];
    }

    /**
     * Keep a row's declared sub-values, drop anything the schema no longer names.
     *
     * @param  mixed  $row
     * @return array<string, mixed>
     */
    protected function reshapeRow($row): array
    {
        $row = is_array($row) ? $row : [];
        $out = [];

        foreach ($this->fields as $field) {
            // No per-row seed: a row has no key to match a blueprint row on.
            [$present, $value] = $field->reconcile($row, []);

            if ($present) {
                $out[$field->id] = $value;
            }
        }

        return $out;
    }
}
