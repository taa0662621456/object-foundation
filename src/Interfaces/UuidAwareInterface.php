<?php

namespace ObjectFoundation\Interfaces;

use Ramsey\Uuid\Uuid;

interface UuidAwareInterface
{
    public function getUuid(): ?Uuid;
}
