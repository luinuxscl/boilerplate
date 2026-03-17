<?php

namespace App\Services\ApiKeys;

class ScopeRegistry
{
    /** @var array<string, array{description: string, group: string}> */
    private array $scopes = [];

    /**
     * @param array<string, array{description: string, group: string}> $scopes
     */
    public function register(array $scopes): void
    {
        $this->scopes = array_merge($this->scopes, $scopes);
    }

    /**
     * @return array<string, array{description: string, group: string}>
     */
    public function all(): array
    {
        return $this->scopes;
    }

    public function exists(string $scope): bool
    {
        return isset($this->scopes[$scope]) || isset($this->scopes['*']);
    }

    /**
     * @return array<string, array<string, array{description: string, group: string}>>
     */
    public function grouped(): array
    {
        $grouped = [];

        foreach ($this->scopes as $scope => $meta) {
            $grouped[$meta['group']][$scope] = $meta;
        }

        return $grouped;
    }
}
