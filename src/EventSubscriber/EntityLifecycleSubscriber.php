<?php

namespace ObjectFoundation\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use ObjectFoundation\Events\{ConfigChangedEvent, EntityCreatedEvent, EntityUpdatedEvent, SoftDeletedEvent};
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

final readonly class EntityLifecycleSubscriber implements EventSubscriber
{
    public function __construct(private EventDispatcherInterface $dispatcher)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->dispatcher->dispatch(new EntityCreatedEvent($entity));
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // Always dispatch generic "updated"
        $this->dispatcher->dispatch(new EntityUpdatedEvent($entity));

        // Heuristics: if entity exposes config getter, treat as config change
        if (method_exists($entity, 'getConfig')) {
            try {
                $config = $entity->getConfig(true);
                if (is_array($config)) {
                    $this->dispatcher->dispatch(new ConfigChangedEvent($entity, $config));
                }
            } catch (Throwable $e) {
                // no-op: entity may not support this fully
            }
        }

        // Heuristics: soft delete detection (boolean isDeleted + optional deletedBy)
        if (method_exists($entity, 'isDeleted') && $entity->isDeleted()) {
            $deletedBy = null;
            if (method_exists($entity, 'getDeletedBy')) {
                $deletedBy = $entity->getDeletedBy();
            }
            $this->dispatcher->dispatch(new SoftDeletedEvent($entity, $deletedBy));
        }
    }
}
