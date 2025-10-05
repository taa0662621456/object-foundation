<?php
namespace ObjectFoundation\Traits;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait SoftDeleteTrait
{
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $isDeleted = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected ?DateTimeImmutable $deletedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $deletedBy = null;

    public function isDeleted(): bool { return $this->isDeleted; }
    public function getDeletedAt(): ?DateTimeImmutable { return $this->deletedAt; }
    public function getDeletedBy(): ?int { return $this->deletedBy; }

    public function softDelete(?int $userId = null): void
    {
        $this->isDeleted = true;
        $this->deletedAt = new DateTimeImmutable();
        $this->deletedBy = $userId;
    }
}
