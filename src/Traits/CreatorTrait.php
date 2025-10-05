<?php
namespace ObjectFoundation\Traits;

use Doctrine\ORM\Mapping as ORM;

trait CreatorTrait
{
    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    protected int $createdBy = 1;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    protected int $modifiedBy = 1;

    public function getCreatedBy(): int { return $this->createdBy; }
    public function getModifiedBy(): int { return $this->modifiedBy; }
    public function setCreatedBy(int $id): void { $this->createdBy = $id; }
    public function setModifiedBy(int $id): void { $this->modifiedBy = $id; }
}