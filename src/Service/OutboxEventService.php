<?php
declare(strict_types=1);

namespace ObjectFoundation\Service;

// NOTE: Replace with your real OutboxStorage implementation when available
use ObjectFoundation\Events\OutboxStorage;
use Throwable;

final class OutboxEventService
{
    public function append(string $event, array $payload): void
    {
        try {
            if (class_exists(OutboxStorage::class)) {
                (new OutboxStorage())->append($event, $payload);
            }
        } catch (Throwable $e) {
            // swallow
        }
    }
}
