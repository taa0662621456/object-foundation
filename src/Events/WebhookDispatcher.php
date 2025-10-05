<?php
namespace ObjectFoundation\Events;

final class WebhookDispatcher
{
    private array $urls;
    private string $secret;

    public function __construct(?array $urls = null, ?string $secret = null)
    {
        $envUrls = getenv('OBJECT_FOUNDATION_WEBHOOKS') ?: '';
        $this->urls = $urls ?? array_filter(array_map('trim', explode(',', $envUrls)));
        $this->secret = $secret ?? (getenv('OBJECT_FOUNDATION_WEBHOOK_SECRET') ?: '');
    }

    public function dispatch(array $record): bool
    {
        if (empty($this->urls)) return true;
        $payload = json_encode($record, JSON_UNESCAPED_SLASHES);
        $sig = $this->secret ? hash_hmac('sha256', $payload, $this->secret) : '';

        $okAll = true;
        foreach ($this->urls as $url) {
            $ctx = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\n" .
                                ($sig ? "X-Signature: {$sig}\r\n" : ""),
                    'content' => $payload,
                    'timeout' => 5
                ]
            ]);
            $res = @file_get_contents($url, false, $ctx);
            if ($res === false) { $okAll = false; }
        }
        return $okAll;
    }
}