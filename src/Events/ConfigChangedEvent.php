<?php

namespace ObjectFoundation\Events;

final class ConfigChangedEvent
{
    public function __construct(public object $entity, public array $newConfig)
    {
    }
    public function getEntity(): object
    {
        return $this->entity;
    }
    public function getNewConfig(): array
    {
        return $this->newConfig;
    }
}
