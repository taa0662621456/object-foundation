<?php
namespace ObjectFoundation\Interfaces;
interface TimestampableInterface {
    public function getCreatedAt(): \DateTimeImmutable;
    public function getUpdatedAt(): \DateTimeImmutable;
}