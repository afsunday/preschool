<?php

namespace App\Cms\Fields;

class Select extends Field
{
    protected string $type = 'select';

    /** @var array<int, array{value: string, label: string}> */
    protected array $options = [];

    /**
     * @param  array<string, string>|array<int, array{value: string, label: string}>  $options
     */
    public function options(array $options): static
    {
        // Accept ['value' => 'Label'] or [['value'=>, 'label'=>], ...].
        $this->options = array_is_list($options)
            ? $options
            : array_map(
                fn ($label, $value) => ['value' => (string) $value, 'label' => $label],
                $options,
                array_keys($options),
            );

        return $this;
    }

    protected function extra(): array
    {
        return ['options' => $this->options];
    }

    public function sanitize(mixed $value): mixed
    {
        $allowed = array_column($this->options, 'value');

        return in_array($value, $allowed, true) ? $value : ($this->default ?? null);
    }
}
