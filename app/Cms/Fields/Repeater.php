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

        return array_values(array_map(function ($row): array {
            $row = is_array($row) ? $row : [];
            $out = [];
            foreach ($this->fields as $field) {
                if (array_key_exists($field->id, $row)) {
                    $out[$field->id] = $field->sanitize($row[$field->id]);
                }
            }

            return $out;
        }, $value));
    }
}
