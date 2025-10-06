<?php
namespace ObjectFoundation\Bridge\Symfony\Traits;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait LockableTrait
{
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected ?DateTimeImmutable $lockedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $lockedBy = null;

    public function getLockedAt(): ?DateTimeImmutable { return $this->lockedAt; }
    public function getLockedBy(): ?int { return $this->lockedBy; }

    public function lock(?int $userId = null): void
    {
        $this->lockedAt = new DateTimeImmutable();
        $this->lockedBy = $userId;
    }

    public function unlock(): void
    {
        $this->lockedAt = null;
        $this->lockedBy = null;
    }
}
