<?php
namespace ObjectFoundation\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class WorkflowStateVO
{
    #[ORM\Column(type: 'string', length: 64)]
    private string $state = 'submitted';

    public function __construct(string $state = 'submitted')
    {
        $this->state = $state;
    }

    public function getState(): string { return $this->state; }
    public function setState(string $state): void { $this->state = $state; }
}