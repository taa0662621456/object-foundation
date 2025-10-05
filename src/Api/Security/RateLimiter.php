<?php
namespace ObjectFoundation\Api\Security;

final class RateLimiter
{
    private string $storage;
    private int $limit;
    private int $window;

    public function __construct(
        string $storageFile = 'var/runtime/ratelimit.json',
        int $limit = 60,
        int $windowSeconds = 60
    ) {
        $this->storage = $storageFile;
        $this->limit = $limit;
        $this->window = $windowSeconds;
        @mkdir(dirname($this->storage), 0777, true);
        if (!file_exists($this->storage)) file_put_contents($this->storage, json_encode([]));
    }

    public function allow(string $key): bool
    {
        $now = time();
        $data = json_decode(file_get_contents($this->storage), true) ?: [];
        $entry = $data[$key] ?? ['count' => 0, 'reset' => $now + $this->window];

        if ($now > ($entry['reset'] ?? 0)) {
            $entry = ['count' => 0, 'reset' => $now + $this->window];
        }

        if ($entry['count'] >= $this->limit) {
            $data[$key] = $entry;
            file_put_contents($this->storage, json_encode($data));
            return false;
        }

        $entry['count']++;
        $data[$key] = $entry;
        file_put_contents($this->storage, json_encode($data));
        return true;
    }

    public function headers(string $key): array
    {
        $now = time();
        $data = json_decode(file_get_contents($this->storage), true) ?: [];
        $entry = $data[$key] ?? ['count' => 0, 'reset' => $now + $this->window];
        return [
            'X-RateLimit-Limit' => (string)$this->limit,
            'X-RateLimit-Remaining' => (string)max(0, $this->limit - (int)$entry['count']),
            'X-RateLimit-Reset' => (string)($entry['reset'] ?? ($now + $this->window)),
        ];
    }
}