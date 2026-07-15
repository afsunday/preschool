<?php

namespace App\Cms\Fields;

class Number extends Field
{
    protected string $type = 'number';

    public function sanitize(mixed $value): mixed
    {
        return is_numeric($value) ? $value + 0 : null;
    }
}
