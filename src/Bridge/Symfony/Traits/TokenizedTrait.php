<?php

namespace ObjectFoundation\Bridge\Symfony\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait TokenizedTrait
{
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Assert\NotBlank(allowNull: true)]
    protected ?string $token = null;

    public function getToken(): ?string
    {
        return $this->token;
    }
    public function setToken(?string $token): void
    {
        $this->token = $token;
    }
}
