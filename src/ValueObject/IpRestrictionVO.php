<?php
namespace ObjectFoundation\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class IpRestrictionVO
{
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $ips = [];

    public function allow(string $ip): void
    {
        $list = $this->ips ?? [];
        if (!in_array($ip, $list, true)) $list[] = $ip;
        $this->ips = $list;
    }

    public function deny(string $ip): void
    {
        $list = array_filter($this->ips ?? [], fn($x) => $x !== $ip);
        $this->ips = array_values($list);
    }

    public function all(): array { return $this->ips ?? []; }

    public function isAllowed(string $ip): bool
    {
        $list = $this->ips ?? [];
        return empty($list) || in_array($ip, $list, true);
    }
}