<?php

namespace ObjectFoundation\Cache;

use ObjectFoundation\Ontology\Support\ManifestCollector;

final class ManifestCache
{
    private CacheAdapter $adapter;
    private int $ttl;

    public function __construct(?CacheAdapter $adapter = null, ?int $ttl = null)
    {
        $redisUrl = getenv('REDIS_URL') ?: null;
        if ($adapter) {
            $this->adapter = $adapter;
        } elseif ($redisUrl && class_exists('\Redis')) {
            $this->adapter = new RedisCacheAdapter($redisUrl);
        } else {
            $this->adapter = new FileCacheAdapter(getenv('OBJECT_FOUNDATION_CACHE_DIR') ?: 'var/cache/manifests');
        }
        $this->ttl = $ttl ?? (int)(getenv('OBJECT_FOUNDATION_CACHE_TTL') ?: 3600);
    }

    public function manifestFor(string $fqcn, ManifestCollector $collector): array
    {
        $key = 'manifest:'.$fqcn;
        $hit = $this->adapter->get($key);
        if ($hit && ($hit['expires'] ?? 0) > time()) {
            return $hit['data'];
        }
        $data = $collector->manifestFor($fqcn);
        $this->adapter->set($key, ['data' => $data, 'ts' => time()], $this->ttl);
        return $data;
    }

    public function result(string $cacheKey, callable $compute): array
    {
        $key = 'result:'.$cacheKey;
        $hit = $this->adapter->get($key);
        if ($hit && ($hit['expires'] ?? 0) > time()) {
            return $hit['data'];
        }
        $data = $compute();
        $this->adapter->set($key, ['data' => $data, 'ts' => time()], $this->ttl);
        return $data;
    }

    public function clear(): void
    {
        $this->adapter->clear();
    }
}
