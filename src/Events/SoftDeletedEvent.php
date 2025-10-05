<?php
namespace ObjectFoundation\Events;

final class SoftDeletedEvent
{
    public function __construct(public object $entity, public ?int $deletedBy = null) {}
    public function getEntity(): object { return $this->entity; }
    public function getDeletedBy(): ?int { return $this->deletedBy; }
}