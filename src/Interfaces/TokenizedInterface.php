<?php
namespace ObjectFoundation\Interfaces;
interface TokenizedInterface {
    public function getToken(): ?string;
    public function setToken(?string $token): void;
}