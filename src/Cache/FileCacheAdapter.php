<?php
namespace ObjectFoundation\Cache;

final class FileCacheAdapter implements CacheAdapter
{
    public function __construct(private string $dir = 'var/cache/manifests') {
        @mkdir($this->dir, 0777, true);
    }

    private function path(string $key): string
    {
        return rtrim($this->dir, '/').'/'.sha1($key).'.json';
    }

    public function get(string $key): ?array
    {
        $path = $this->path($key);
        if (!is_file($path)) return null;
        $raw = file_get_contents($path);
        if ($raw === false) return null;
        $data = json_decode($raw, true);
        if (!is_array($data)) return null;
        // TTL handled by writer; reader trusts caller
        return $data;
    }

    public function set(string $key, array $value, int $ttl): void
    {
        $value['expires'] = time() + $ttl;
        file_put_contents($this->path($key), json_encode($value, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
    }

    public function delete(string $key): void
    {
        $p = $this->path($key);
        if (is_file($p)) @unlink($p);
    }

    public function clear(): void
    {
        foreach (glob(rtrim($this->dir,'/').'/*.json') as $f) @unlink($f);
    }
}