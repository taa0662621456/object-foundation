<?php
namespace ObjectFoundation\Bridge\Symfony\Traits;

use Doctrine\ORM\Mapping as ORM;

trait WorkFlowTrait
{
    #[ORM\Column(type: 'string', length: 64, options: ['default' => 'submitted'])]
    protected string $workFlow = 'submitted';

    public function getWorkFlow(): string { return $this->workFlow; }
    public function setWorkFlow(string $state): void { $this->workFlow = $state; }
}
