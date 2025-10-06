<?php
declare(strict_types=1);

namespace ObjectFoundation\Http;

final readonly class Response
{
    public function __construct(
        public int    $status,
        public array  $headers = [],
        public string $body = ''
    ) {}

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header($k . ': ' . $v);
        }
        echo $this->body;
    }

    public static function json(mixed $data, int $status = 200, array $extraHeaders = []): self
    {
        $body = is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return new self($status, array_merge(['Content-Type' => 'application/json; charset=utf-8'], $extraHeaders), $body);
    }

    public static function text(string $text, int $status = 200, array $extraHeaders = []): self
    {
        return new self($status, array_merge(['Content-Type' => 'text/plain; charset=utf-8'], $extraHeaders), $text);
    }

    public static function html(string $html, int $status = 200, array $extraHeaders = []): self
    {
        return new self($status, array_merge(['Content-Type' => 'text/html; charset=utf-8'], $extraHeaders), $html);
    }
}
