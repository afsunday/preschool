<?php

namespace App\Cms;

use RuntimeException;

/**
 * Discovers block types by scanning the page views for inline definitions:
 *
 *   @block([ 'key' => 'hero', 'fields' => [...] ])
 *       <section> ... {{ $s->get('title') }} ... </section>
 *   @endblock
 *
 * Schema and template live together, in the page file itself — no sections
 * folder. A block defined in any scanned view is available everywhere.
 */
class SectionRegistry
{
    /** @var array<string, SectionDefinition>|null */
    protected ?array $sections = null;

    /**
     * Page views scanned for @block definitions.
     *
     * @return array<int, string>
     */
    protected function files(): array
    {
        return glob(resource_path('views').'/*.blade.php') ?: [];
    }

    /**
     * @return array<string, SectionDefinition>
     */
    public function all(): array
    {
        if ($this->sections !== null) {
            return $this->sections;
        }

        $this->sections = [];

        foreach ($this->files() as $file) {
            $content = (string) file_get_contents($file);

            if (! str_contains($content, '@block')) {
                continue;
            }

            foreach ($this->parse($content) as [$spec, $template]) {
                if (empty($spec['key'])) {
                    continue;
                }

                $this->sections[$spec['key']] = new SectionDefinition(
                    key: $spec['key'],
                    name: $spec['name'] ?? $spec['key'],
                    group: $spec['group'] ?? 'Content',
                    version: (int) ($spec['version'] ?? 1),
                    acceptsChildren: (bool) ($spec['acceptsChildren'] ?? false),
                    template: trim($template),
                    fieldSpecs: $spec['fields'] ?? [],
                );
            }
        }

        return $this->sections;
    }

    public function find(string $key): ?SectionDefinition
    {
        return $this->all()[$key] ?? null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function schemas(): array
    {
        return array_values(array_map(
            fn (SectionDefinition $s) => $s->schema(),
            $this->all(),
        ));
    }

    /**
     * Extract every @block([...]) ... @endblock pair as [schema array, body].
     *
     * @return array<int, array{0: array<string, mixed>, 1: string}>
     */
    protected function parse(string $content): array
    {
        $blocks = [];
        $offset = 0;

        while (($at = strpos($content, '@block', $offset)) !== false) {
            $open = strpos($content, '(', $at);
            if ($open === false) {
                break;
            }

            $close = $this->matchParen($content, $open);
            if ($close === null) {
                break;
            }

            $end = strpos($content, '@endblock', $close);
            if ($end === false) {
                break;
            }

            $spec = $this->evalArray(substr($content, $open + 1, $close - $open - 1));
            $body = substr($content, $close + 1, $end - $close - 1);

            if (is_array($spec)) {
                $blocks[] = [$spec, $body];
            }

            $offset = $end + strlen('@endblock');
        }

        return $blocks;
    }

    /**
     * Index of the paren matching the one at $open (ignoring quoted parens).
     */
    protected function matchParen(string $content, int $open): ?int
    {
        $depth = 0;
        $len = strlen($content);
        $quote = null;

        for ($i = $open; $i < $len; $i++) {
            $ch = $content[$i];

            if ($quote !== null) {
                if ($ch === $quote && $content[$i - 1] !== '\\') {
                    $quote = null;
                }

                continue;
            }

            if ($ch === '"' || $ch === "'") {
                $quote = $ch;
            } elseif ($ch === '(') {
                $depth++;
            } elseif ($ch === ')') {
                if (--$depth === 0) {
                    return $i;
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function evalArray(string $expr): ?array
    {
        try {
            /** @var mixed $result */
            $result = eval("return {$expr};");
        } catch (\Throwable $e) {
            throw new RuntimeException('Bad @block schema: '.$e->getMessage());
        }

        return is_array($result) ? $result : null;
    }
}
