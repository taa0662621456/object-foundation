<?php
namespace ObjectFoundation\Events;

final class EntityUpdatedEvent
{
    public function __construct(public object $entity) {}
    public function getEntity(): object { return $this->entity; }
}