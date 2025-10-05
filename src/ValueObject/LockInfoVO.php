<?php
namespace ObjectFoundation\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class LockInfoVO
{
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lockedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $lockedBy = null;

    public function lock(?int $userId = null): void
    {
        $this->lockedAt = new \DateTimeImmutable();
        $this->lockedBy = $userId;
    }

    public function unlock(): void
    {
        $this->lockedAt = null;
        $this->lockedBy = null;
    }

    public function getLockedAt(): ?\DateTimeImmutable { return $this->lockedAt; }
    public function getLockedBy(): ?int { return $this->lockedBy; }
}