<?php
namespace ObjectFoundation\Events;

use DateTimeImmutable;
use Redis;

final class OutboxStorage
{
    private string $driver; // file|redis
    private string $file;
    private ?Redis $redis = null;
    private string $prefix = 'of:outbox:';

    /**
     * @throws \RedisException
     */
    public function __construct(?string $driver = null, ?string $file = null)
    {
        $this->driver = $driver ?? (getenv('OBJECT_FOUNDATION_OUTBOX_DRIVER') ?: 'file');
        $this->file = $file ?? (getenv('OBJECT_FOUNDATION_OUTBOX_FILE') ?: 'var/outbox/events.json');
        if ($this->driver === 'redis') {
            $url = getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379/0';
            $this->redis = new Redis();
            $parts = parse_url($url);
            $host = $parts['host'] ?? '127.0.0.1';
            $port = (int)($parts['port'] ?? 6379);
            $pass = $parts['pass'] ?? null;
            $db   = isset($parts['path']) ? (int)trim($parts['path'], '/') : 0;
            $this->redis->connect($host, $port, 1.5);
            if ($pass) $this->redis->auth($pass);
            if ($db) $this->redis->select($db);
        } else {
            @mkdir(dirname($this->file), 0777, true);
            if (!file_exists($this->file)) file_put_contents($this->file, json_encode([]));
        }
    }

    /**
     * @throws \RedisException
     */
    public function append(string $event, array $payload): string
    {
        $rec = [
            'id' => $this->uuid(),
            'event' => $event,
            'payload' => $payload,
            'created_at' => (new DateTimeImmutable())->format('c'),
            'dispatched' => false,
            'attempts' => 0,
            'last_error' => null
        ];
        if ($this->driver === 'redis' && $this->redis) {
            $this->redis->rPush($this->prefix.'queue', json_encode($rec, JSON_UNESCAPED_SLASHES));
        } else {
            $data = $this->readFile();
            $data[] = $rec;
            $this->writeFile($data);
        }
        return $rec['id'];
    }

    /**
     * @throws \RedisException
     */
    public function allUndispatched(int $limit = 100): array
    {
        if ($this->driver === 'redis' && $this->redis) {
            // Peek items
            $len = (int)$this->redis->lLen($this->prefix.'queue');
            $len = min($len, $limit);
            $out = [];
            for ($i = 0; $i < $len; $i++) {
                $item = $this->redis->lIndex($this->prefix.'queue', $i);
                if ($item) $out[] = json_decode($item, true);
            }
            return $out;
        }
        $data = $this->readFile();
        return array_values(array_filter($data, fn($r) => !$r['dispatched']));
    }

    /**
     * @throws \RedisException
     */
    public function markDispatched(string $id): void
    {
        if ($this->driver === 'redis' && $this->redis) {
            // Remove first matching element
            $list = $this->redis->lRange($this->prefix.'queue', 0, -1);
            foreach ($list as $el) {
                $r = json_decode($el, true);
                if (($r['id'] ?? '') === $id) {
                    $this->redis->lRem($this->prefix.'queue', $el, 1);
                    break;
                }
            }
            return;
        }
        $data = $this->readFile();
        foreach ($data as &$r) {
            if ($r['id'] === $id) { $r['dispatched'] = true; break; }
        }
        $this->writeFile($data);
    }

    public function incrementAttempt(string $id, string $error = ''): void
    {
        if ($this->driver === 'redis' && $this->redis) {
            return; // skip bookkeeping for redis
        }
        $data = $this->readFile();
        foreach ($data as &$r) {
            if ($r['id'] === $id) { $r['attempts'] = (int)$r['attempts'] + 1; $r['last_error'] = $error; break; }
        }
        $this->writeFile($data);
    }

    /**
     * @throws \RedisException
     */
    public function purgeDispatched(): int
    {
        if ($this->driver === 'redis' && $this->redis) {
            $this->redis->del($this->prefix.'queue');
            return 0;
        }
        $data = $this->readFile();
        $before = count($data);
        $data = array_values(array_filter($data, fn($r) => !$r['dispatched']));
        $this->writeFile($data);
        return $before - count($data);
    }

    private function readFile(): array
    {
        $raw = file_get_contents($this->file) ?: '[]';
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function writeFile(array $data): void
    {
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }

    /**
     * @throws \Exception
     */
    private function uuid(): string
    {
        $d = random_bytes(16);
        $d[6] = chr((ord($d[6]) & 0x0f) | 0x40);
        $d[8] = chr((ord($d[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
    }
}
