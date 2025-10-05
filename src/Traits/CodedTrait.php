<?php
namespace ObjectFoundation\Traits;

use Doctrine\ORM\Mapping as ORM;

trait CodedTrait
{
    #[ORM\Column(type: 'string', length: 191, unique: true, nullable: true)]
    protected ?string $code = null;

    public function getCode(): ?string { return $this->code; }
    public function setCode(?string $code): void { $this->code = $code; }
}