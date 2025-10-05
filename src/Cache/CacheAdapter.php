<?php
namespace ObjectFoundation\Cache;

interface CacheAdapter
{
    public function get(string $key): ?array;           // returns ['data'=>mixed,'ts'=>int]
    public function set(string $key, array $value, int $ttl): void;
    public function delete(string $key): void;
    public function clear(): void;
}