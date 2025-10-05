<?php
namespace ObjectFoundation\Interfaces;
use DateTimeImmutable;

interface LockableInterface {
    public function getLockedAt(): ?DateTimeImmutable;
}
