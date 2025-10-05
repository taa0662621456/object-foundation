<?php
namespace ObjectFoundation\Interfaces;
interface ConfigurableInterface {
    public function getConfig(bool $decrypted = true): ?array;
    public function setConfig(array $config): void;
}