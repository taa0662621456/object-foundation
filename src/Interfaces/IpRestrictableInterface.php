<?php
namespace ObjectFoundation\Interfaces;
interface IpRestrictableInterface {
    public function isIpAllowed(string $ip): bool;
}