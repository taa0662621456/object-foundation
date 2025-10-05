<?php
namespace ObjectFoundation\Interfaces;
interface CodedInterface {
    public function getCode(): ?string;
    public function setCode(?string $code): void;
}