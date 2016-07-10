<?php

namespace steevanb\DoctrineReadOnlyHydrator\Bridge\Symfony3;

use steevanb\DoctrineReadOnlyHydrator\Bridge\Symfony3\DependencyInjection\Compiler\AddReadOnlyHydrationModePass;
use steevanb\DoctrineReadOnlyHydrator\Bridge\Symfony3\DependencyInjection\Compiler\AddSimpleObjectHydrationModePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ReadOnlyHydratorBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new AddReadOnlyHydrationModePass())
            ->addCompilerPass(new AddSimpleObjectHydrationModePass());
    }
}
