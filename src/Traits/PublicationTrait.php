<?php
namespace ObjectFoundation\Traits;

use Doctrine\ORM\Mapping as ORM;

trait PublicationTrait
{
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    protected bool $published = true;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected ?\DateTimeImmutable $expiresAt = null;

    public function isPublished(): bool { return $this->published; }
    public function setPublished(bool $flag): void { $this->published = $flag; }

    public function getExpiresAt(): ?\DateTimeImmutable { return $this->expiresAt; }
    public function setExpiresAt(?\DateTimeImmutable $dt): void { $this->expiresAt = $dt; }
}