<?php
namespace ObjectFoundation\Cache;

use Redis;

final class RedisCacheAdapter implements CacheAdapter
{
    private Redis $redis;
    private string $prefix;

    public function __construct(string $url, string $prefix = 'of:cache:')
    {
        $this->prefix = $prefix;
        $this->redis = new Redis();
        $parts = parse_url($url);
        $host = $parts['host'] ?? '127.0.0.1';
        $port = (int)($parts['port'] ?? 6379);
        $pass = $parts['pass'] ?? null;
        $db   = isset($parts['path']) ? (int)trim($parts['path'], '/') : 0;
        $this->redis->connect($host, $port, 1.5);
        if ($pass) $this->redis->auth($pass);
        if ($db) $this->redis->select($db);
    }

    private function k(string $key): string { return $this->prefix . sha1($key); }

    public function get(string $key): ?array
    {
        $v = $this->redis->get($this->k($key));
        if (!$v) return null;
        $data = json_decode($v, true);
        return is_array($data) ? $data : null;
    }

    public function set(string $key, array $value, int $ttl): void
    {
        $value['expires'] = time() + $ttl;
        $this->redis->set($this->k($key), json_encode($value, JSON_UNESCAPED_SLASHES), $ttl);
    }

    public function delete(string $key): void
    {
        $this->redis->del($this->k($key));
    }

    public function clear(): void
    {
        // delete by prefix (SCAN)
        $it = NULL;
        while ($arr_keys = $this->redis->scan($it, $this->prefix.'*')) {
            foreach ($arr_keys as $k) { $this->redis->del($k); }
        }
    }
}
