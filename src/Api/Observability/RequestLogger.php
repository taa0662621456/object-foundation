<?php
namespace ObjectFoundation\Api\Observability;

use DateTimeImmutable;

final class RequestLogger
{
    private string $file;
    private readonly string $level;

    public function __construct(?string $file = null, ?string $level = null)
    {
        $this->file = $file ?? (getenv('OBJECT_FOUNDATION_LOG_FILE') ?: 'var/log/api.log');
        $this->level = strtolower($level ?? (getenv('OBJECT_FOUNDATION_LOG_LEVEL') ?: 'info'));
        @mkdir(dirname($this->file), 0777, true);
    }

    public function log(array $data): void
    {
        $data['ts'] = (new DateTimeImmutable())->format('c');
        $json = json_encode($data, JSON_UNESCAPED_SLASHES);
        file_put_contents($this->file, $json . PHP_EOL, FILE_APPEND);
    }
}
