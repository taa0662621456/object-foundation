<?php
namespace ObjectFoundation\Interfaces;

interface EncryptableInterface
{
    public function encryptConfig(string $key, string $cipher = 'AES-256-CBC'): void;
    public function decryptConfig(string $key, string $cipher = 'AES-256-CBC'): void;
}