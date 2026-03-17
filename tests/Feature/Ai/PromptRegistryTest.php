<?php

use App\Models\AiPrompt;
use App\Services\Ai\PromptRegistry;

beforeEach(function (): void {
    $this->registry = new PromptRegistry();
});

it('registers and renders an in-memory prompt', function (): void {
    $this->registry->register('welcome', [
        'template' => 'Hello, {{name}}!',
    ]);

    $rendered = $this->registry->render('welcome', ['name' => 'Bob']);

    expect($rendered)->toBe('Hello, Bob!');
});

it('leaves unresolved placeholders intact', function (): void {
    $this->registry->register('partial', [
        'template' => 'Hello, {{name}}! You have {{count}} messages.',
    ]);

    $rendered = $this->registry->render('partial', ['name' => 'Alice']);

    expect($rendered)->toBe('Hello, Alice! You have {{count}} messages.');
});

it('db prompt overrides in-memory prompt', function (): void {
    $this->registry->register('override', [
        'template' => 'in-memory template',
    ]);

    AiPrompt::create([
        'name'      => 'override',
        'template'  => 'db template for {{who}}',
        'is_active' => true,
    ]);

    $rendered = $this->registry->render('override', ['who' => 'world']);

    expect($rendered)->toBe('db template for world');
});

it('inactive db prompt does not override in-memory', function (): void {
    $this->registry->register('inactive', [
        'template' => 'in-memory result',
    ]);

    AiPrompt::create([
        'name'      => 'inactive',
        'template'  => 'db template',
        'is_active' => false,
    ]);

    $rendered = $this->registry->render('inactive');

    expect($rendered)->toBe('in-memory result');
});

it('throws RuntimeException for unknown prompt', function (): void {
    expect(fn () => $this->registry->render('nonexistent'))
        ->toThrow(\RuntimeException::class);
});

it('all() returns in-memory registered prompts', function (): void {
    $this->registry->register('a', ['template' => 'A']);
    $this->registry->register('b', ['template' => 'B']);

    expect($this->registry->all())->toHaveKeys(['a', 'b']);
});

it('findModel() returns null when prompt does not exist in db', function (): void {
    expect($this->registry->findModel('missing'))->toBeNull();
});

it('findModel() returns AiPrompt when it exists in db', function (): void {
    AiPrompt::create([
        'name'      => 'db-one',
        'template'  => 'Something',
        'is_active' => true,
    ]);

    expect($this->registry->findModel('db-one'))->toBeInstanceOf(AiPrompt::class);
});
