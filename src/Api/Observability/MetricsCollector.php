<?php
namespace ObjectFoundation\Api\Observability;

use Redis;

final class MetricsCollector
{
    private string $storage;
    private ?Redis $redis = null;
    private string $prefix = 'of:metrics:';

    /**
     * @throws \RedisException
     */
    public function __construct(?string $storage = null)
    {
        $this->storage = $storage ?? (getenv('OBJECT_FOUNDATION_METRICS_FILE') ?: 'var/metrics/metrics.json');
        @mkdir(dirname($this->storage), 0777, true);

        $redisUrl = getenv('REDIS_URL') ?: null;
        if ($redisUrl && class_exists('\Redis')) {
            $this->redis = new Redis();
            $parts = parse_url($redisUrl);
            $host = $parts['host'] ?? '127.0.0.1';
            $port = (int)($parts['port'] ?? 6379);
            $pass = $parts['pass'] ?? null;
            $db   = isset($parts['path']) ? (int)trim($parts['path'], '/') : 0;
            $this->redis->connect($host, $port, 1.5);
            if ($pass) $this->redis->auth($pass);
            if ($db) $this->redis->select($db);
        }
    }

    /**
     * @throws \RedisException
     */
    public function addRequest(float $durationMs, int $status, bool $cacheHit = false, bool $authFail = false): void
    {
        if ($this->redis) {
            $this->redis->incrByFloat($this->prefix.'sum_latency', $durationMs);
            $this->redis->incr($this->prefix.'count_total');
            $this->redis->incr($this->prefix.'status:'.$status);
            if ($cacheHit) $this->redis->incr($this->prefix.'cache_hit');
            if ($authFail) $this->redis->incr($this->prefix.'auth_fail');
            return;
        }
        $data = $this->readFile();
        $data['sum_latency'] = ($data['sum_latency'] ?? 0) + $durationMs;
        $data['count_total'] = ($data['count_total'] ?? 0) + 1;
        $data['status'][$status] = ($data['status'][$status] ?? 0) + 1;
        $data['cache_hit'] = ($data['cache_hit'] ?? 0) + ($cacheHit ? 1 : 0);
        $data['auth_fail'] = ($data['auth_fail'] ?? 0) + ($authFail ? 1 : 0);
        $this->writeFile($data);
    }

    /**
     * @throws \RedisException
     */
    public function snapshot(): array
    {
        if ($this->redis) {
            $count = (int)($this->redis->get($this->prefix.'count_total') ?: 0);
            $sum   = (float)($this->redis->get($this->prefix.'sum_latency') ?: 0);
            $cache = (int)($this->redis->get($this->prefix.'cache_hit') ?: 0);
            $authf = (int)($this->redis->get($this->prefix.'auth_fail') ?: 0);
            $statuses = [];
            // collect common statuses
            foreach ([200,201,204,400,401,403,404,429,500] as $st) {
                $v = (int)($this->redis->get($this->prefix.'status:'.$st) ?: 0);
                if ($v) $statuses[(string)$st] = $v;
            }
            return [
                'count_total' => $count,
                'sum_latency' => $sum,
                'avg_latency' => $count ? $sum / $count : 0,
                'status'      => $statuses,
                'cache_hit'   => $cache,
                'auth_fail'   => $authf
            ];
        }
        $data = $this->readFile();
        $count = (int)($data['count_total'] ?? 0);
        $sum   = (float)($data['sum_latency'] ?? 0.0);
        $data['avg_latency'] = $count ? $sum / $count : 0.0;
        return $data;
    }

    public function toPrometheus(array $snap): string
    {
        $lines = [];
        $lines[] = "# HELP of_requests_total Total number of HTTP requests";
        $lines[] = "# TYPE of_requests_total counter";
        $lines[] = "of_requests_total " . (int)($snap['count_total'] ?? 0);
        $lines[] = "# HELP of_request_latency_ms_avg Average request latency in ms";
        $lines[] = "# TYPE of_request_latency_ms_avg gauge";
        $lines[] = "of_request_latency_ms_avg " . (float)($snap['avg_latency'] ?? 0);
        $lines[] = "# HELP of_cache_hits_total Total cache hits";
        $lines[] = "# TYPE of_cache_hits_total counter";
        $lines[] = "of_cache_hits_total " . (int)($snap['cache_hit'] ?? 0);
        $lines[] = "# HELP of_auth_fail_total Total auth failures";
        $lines[] = "# TYPE of_auth_fail_total counter";
        $lines[] = "of_auth_fail_total " . (int)($snap['auth_fail'] ?? 0);
        if (!empty($snap['status'])) {
            $lines[] = "# HELP of_status_code_total Total requests by HTTP status code";
            $lines[] = "# TYPE of_status_code_total counter";
            foreach ($snap['status'] as $code => $cnt) {
                $lines[] = "of_status_code_total{code=\"$code\"} " . (int)$cnt;
            }
        }
        return implode("\n", $lines) . "\n";
    }

    private function readFile(): array
    {
        if (!is_file($this->storage)) return [];
        $raw = file_get_contents($this->storage);
        $data = json_decode($raw ?: "[]", true);
        return is_array($data) ? $data : [];
    }

    private function writeFile(array $data): void
    {
        file_put_contents($this->storage, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }
}
