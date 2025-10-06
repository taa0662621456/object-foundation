<?php
namespace Examples\SymfonyDemo\Entity;

use Doctrine\ORM\Mapping as ORM;
use ObjectFoundation\Bridge\Symfony\Traits\EntityFoundationTrait;

#[ORM\Entity]
#[ORM\Table(name: 'demo_entity')]
#[ORM\HasLifecycleCallbacks]
class DemoEntity
{
    use EntityFoundationTrait;
}
