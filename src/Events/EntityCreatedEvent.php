<?php

namespace ObjectFoundation\Events;

final class EntityCreatedEvent
{
    public function __construct(public object $entity)
    {
    }
    public function getEntity(): object
    {
        return $this->entity;
    }
}
