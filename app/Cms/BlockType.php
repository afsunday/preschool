<?php

namespace App\Cms;

use App\Cms\Fields\Field;
use App\Cms\Fields\Repeater;

/**
 * A block type: what fields a block of this kind has.
 *
 * Read from the `blockTypes` map in a blueprint. It carries no data and renders
 * nothing — its whole job is telling the editor how to edit a block's
 * `settings`, and enforcing that shape on save.
 */
class BlockType
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
     * Reconcile stored settings against this schema on pull.
     *
     * Structural, not semantic: it makes the *set of keys* current without
     * touching content you've edited. A field the block already has keeps its
     * value; a brand-new field is filled from the blueprint seed; a field the
     * schema dropped is pruned. Repeaters keep their rows and lose only removed
     * sub-keys — see {@see Repeater::reconcile()}.
     *
     * A rename is not inferred (old key gone, new key seeded), so its old value
     * is lost — reshape those with {@see migrate()} and a version bump instead.
     *
     * @param  array<string, mixed>  $stored
     * @param  array<string, mixed>  $seed
     * @return array<string, mixed>
     */
    public function reconcile(array $stored, array $seed = []): array
    {
        $clean = [];

        foreach ($this->fields() as $field) {
            [$present, $value] = $field->reconcile($stored, $seed);

            if ($present) {
                $clean[$field->id] = $value;
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
