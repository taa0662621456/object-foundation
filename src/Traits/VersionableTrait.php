<?php
namespace ObjectFoundation\Traits;

use Doctrine\ORM\Mapping as ORM;

trait VersionableTrait
{
    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    protected int $version = 1;

    public function getVersion(): int { return $this->version; }
}