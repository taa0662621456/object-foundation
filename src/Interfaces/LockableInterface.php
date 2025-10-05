<?php
namespace ObjectFoundation\Interfaces;
interface LockableInterface {
    public function getLockedAt(): ?\DateTimeImmutable;
}