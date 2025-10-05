<?php
namespace ObjectFoundation\Interfaces;
use Symfony\Component\Uid\Uuid;
interface UuidAwareInterface {
    public function getUuid(): ?Uuid;
}