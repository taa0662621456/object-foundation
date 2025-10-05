<?php
namespace ObjectFoundation\Reaction;

use DateTimeImmutable;
use ObjectFoundation\Events\{EntityCreatedEvent, EntityUpdatedEvent, SoftDeletedEvent, ConfigChangedEvent};

final class AutoLogReaction
{
    public function __construct(private readonly string $logFile = 'var/export/foundation.log') {}

    public function __invoke(object $event): void
    {
        $type = get_class($event);
        $entity = method_exists($event, 'getEntity') ? get_class($event->getEntity()) : 'unknown';
        $line = sprintf('[%s] %s on %s', (new DateTimeImmutable())->format('c'), $type, $entity);
        @mkdir(dirname($this->logFile), 0777, true);
        file_put_contents($this->logFile, $line . PHP_EOL, FILE_APPEND);
    }
}
