<?php
namespace ObjectFoundation\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

trait IdentityTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    protected ?Uuid $uuid = null;

    #[ORM\Column(type: 'string', length: 191, unique: true, nullable: true)]
    protected ?string $slug = null;

    public function getId(): ?int { return $this->id; }
    public function getUuid(): ?Uuid { return $this->uuid; }
    public function getSlug(): ?string { return $this->slug; }

    public function setSlug(?string $slug): void { $this->slug = $slug; }

    #[ORM\PrePersist]
    public function _identityInit(): void
    {
        if (!$this->uuid) $this->uuid = Uuid::v4();
        if (!$this->slug) $this->slug = (string)$this->uuid;
    }
}