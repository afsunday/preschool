<?php

namespace App\Cms;

use App\Cms\Fields\Field;

/**
 * A block type, built from the `@schema(...)` declared in its Blade template.
 * Replaces the old per-type PHP class — schema and template now live together.
 */
class SectionDefinition
{
    /** @var array<int, Field>|null */
    protected ?array $fieldObjects = null;

    /**
     * @param  array<int, array<string, mixed>>  $fieldSpecs
     */
    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly string $group,
        public readonly int $version,
        public readonly bool $acceptsChildren,
        public readonly string $template,
        protected array $fieldSpecs,
    ) {}

    /**
     * @return array<int, Field>
     */
    public function fields(): array
    {
        return $this->fieldObjects ??= array_map(
            [FieldFactory::class, 'make'],
            $this->fieldSpecs,
        );
    }

    /**
     * The serialised schema the editor + headless consumers read.
     *
     * @return array<string, mixed>
     */
    public function schema(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'group' => $this->group,
            'version' => $this->version,
            'acceptsChildren' => $this->acceptsChildren,
            'fields' => array_map(fn (Field $f) => $f->jsonSerialize(), $this->fields()),
        ];
    }

    /**
     * Enforce the schema: keep only defined fields, sanitize each by type.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public function validate(array $settings): array
    {
        $clean = [];

        foreach ($this->fields() as $field) {
            if (array_key_exists($field->id, $settings)) {
                $clean[$field->id] = $field->sanitize($settings[$field->id]);
            }
        }

        return $clean;
    }

    /**
     * Version migration hook. Blade-declared blocks don't carry code, so this is
     * a no-op by default; reshape older data with a one-off command if needed.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public function migrate(array $settings, int $fromVersion): array
    {
        return $settings;
    }
}
