<?php
namespace ObjectFoundation\Traits;

use Doctrine\ORM\Mapping as ORM;

trait RestrictableTrait
{
    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $ipRestriction = [];

    public function getIpRestriction(): array { return $this->ipRestriction ?? []; }
    public function setIpRestriction(array $ips): void { $this->ipRestriction = $ips; }
    public function isIpAllowed(string $ip): bool
    {
        if (empty($this->ipRestriction)) return true;
        return in_array($ip, $this->ipRestriction, true);
    }
}