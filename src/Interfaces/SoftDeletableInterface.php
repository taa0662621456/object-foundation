<?php
namespace ObjectFoundation\Interfaces;
interface SoftDeletableInterface {
    public function isDeleted(): bool;
    public function getDeletedAt(): ?\DateTimeImmutable;
}