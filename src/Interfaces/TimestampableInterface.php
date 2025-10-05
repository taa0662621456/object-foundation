<?php
namespace ObjectFoundation\Interfaces;
use DateTimeImmutable;

interface TimestampableInterface {
    public function getCreatedAt(): DateTimeImmutable;
    public function getUpdatedAt(): DateTimeImmutable;
}
