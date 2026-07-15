<?php

use App\Cms\SectionDefinition;
use App\Cms\SectionRegistry;

test('a section blade @schema serialises to the field-schema JSON', function () {
    $schema = app(SectionRegistry::class)->find('hero')->schema();

    expect($schema['key'])->toBe('hero');
    expect($schema['name'])->toBe('Hero');
    expect($schema['group'])->toBe('Headers');
    expect($schema['version'])->toBe(1);

    $byId = collect($schema['fields'])->keyBy('id');

    expect($byId['title'])->toMatchArray(['type' => 'text', 'required' => true]);
    expect($byId['title']['label'])->toBe('Title');            // auto from id
    expect($byId['image'])->toMatchArray(['type' => 'media', 'kind' => 'image']);
    expect($byId['align'])->toMatchArray(['type' => 'select', 'default' => 'center']);
    expect($byId['align']['options'])->toContain(['value' => 'center', 'label' => 'Center']);
    expect($byId['body']['type'])->toBe('richtext');
});

test('the registry discovers blocks by scanning the section templates', function () {
    $registry = new SectionRegistry;

    expect($registry->find('hero'))->toBeInstanceOf(SectionDefinition::class);
    expect(collect($registry->schemas())->pluck('key'))
        ->toContain('hero')
        ->toContain('home_hero')
        ->toContain('steps');
});
