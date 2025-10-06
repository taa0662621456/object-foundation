<?php
namespace ObjectFoundation\Bridge\Symfony;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ObjectFoundationBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        // CompilerPass could be added here to auto-register reactions/commands.
    }
}
