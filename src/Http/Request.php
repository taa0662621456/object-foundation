<?php
declare(strict_types=1);

namespace ObjectFoundation\Http;

final readonly class Request
{
    public function __construct(
        public string $method,
        public string $path,
        public array  $headers,
        public array  $query,
        public array  $body,
        public string $ip,
        public float  $startedAt,
    ) {}

    public static function fromGlobals(): self
    {
        $raw = file_get_contents('php://input') ?: '';
        $decoded = json_decode($raw, true);
        $body = is_array($decoded) ? $decoded : [];
        $headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
        // normalize to Title-Case & also keep lowercase mapping
        $norm = [];
        foreach ($headers as $k => $v) {
            $norm[$k] = $v;
            $norm[strtolower($k)] = $v;
            $norm[ucwords(strtolower($k), '-')] = $v;
        }

        return new self(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH),
            $norm,
            $_GET ?? [],
            $body,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            microtime(true)
        );
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $kLower = strtolower($name);
        return $this->headers[$name] ?? $this->headers[$kLower] ?? $this->headers[ucwords($kLower, "-")] ?? $default;
    }
}
