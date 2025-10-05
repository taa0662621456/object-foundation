<?php
namespace ObjectFoundation\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class TimestampVO
{
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(?\DateTimeImmutable $createdAt = null, ?\DateTimeImmutable $updatedAt = null)
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $createdAt ?? $now;
        $this->updatedAt = $updatedAt ?? $now;
    }

    public function touch(): void { $this->updatedAt = new \DateTimeImmutable(); }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}