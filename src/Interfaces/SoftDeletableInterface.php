<?php
namespace ObjectFoundation\Interfaces;
use DateTimeImmutable;

interface SoftDeletableInterface {
    public function isDeleted(): bool;
    public function getDeletedAt(): ?DateTimeImmutable;
}
