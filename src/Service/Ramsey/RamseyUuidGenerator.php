<?php
namespace ObjectFoundation\Service\Ramsey;
use ObjectFoundation\Interfaces\UuidGeneratorInterface;
use Ramsey\Uuid\Uuid;

final class RamseyUuidGenerator implements UuidGeneratorInterface {
    public function v4(): string {
        return (string) Uuid::uuid4();
    }
}
