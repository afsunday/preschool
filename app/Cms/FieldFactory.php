<?php

namespace App\Cms;

use App\Cms\Fields\Color;
use App\Cms\Fields\Field;
use App\Cms\Fields\Media;
use App\Cms\Fields\Number;
use App\Cms\Fields\Relation;
use App\Cms\Fields\Repeater;
use App\Cms\Fields\RichText;
use App\Cms\Fields\Select;
use App\Cms\Fields\Text;
use App\Cms\Fields\Textarea;
use App\Cms\Fields\Url;

/**
 * Builds a Field object from an array spec (as declared in a section blade's
 * `@schema(...)`). Keeps all the sanitize/serialize logic in the Field classes
 * while letting authors declare fields as plain arrays next to their template.
 */
class FieldFactory
{
    /**
     * @param  array<string, mixed>  $spec
     */
    public static function make(array $spec): Field
    {
        $id = (string) ($spec['id'] ?? '');
        $label = $spec['label'] ?? null;

        $field = match ($spec['type'] ?? 'text') {
            'textarea' => new Textarea($id, $label),
            'richtext' => new RichText($id, $label),
            'number' => new Number($id, $label),
            'url' => new Url($id, $label),
            'color' => new Color($id, $label),
            'select' => (new Select($id, $label))->options($spec['options'] ?? []),
            'media' => self::media($id, $label, $spec),
            'repeater' => (new Repeater($id, $label))->fields(
                array_map([self::class, 'make'], $spec['fields'] ?? []),
            ),
            'relation' => (new Relation($id, $label))->source($spec['source'] ?? ''),
            default => new Text($id, $label),
        };

        if (! empty($spec['required'])) {
            $field->required();
        }

        if (array_key_exists('default', $spec)) {
            $field->default($spec['default']);
        }

        return $field;
    }

    /**
     * @param  array<string, mixed>  $spec
     */
    protected static function media(string $id, ?string $label, array $spec): Media
    {
        $field = new Media($id, $label);

        if (! empty($spec['kind'])) {
            $field->kind($spec['kind']);
        }

        if (! empty($spec['multiple'])) {
            $field->multiple();
        }

        return $field;
    }
}
