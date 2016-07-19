<?php

namespace steevanb\DoctrineReadOnlyHydrator\Bridge\ReadOnlyHydratorBundle;

use steevanb\DoctrineReadOnlyHydrator\Bridge\ReadOnlyHydratorBundle\DependencyInjection\Compiler\AddReadOnlyHydrationModePass;
use steevanb\DoctrineReadOnlyHydrator\Bridge\ReadOnlyHydratorBundle\DependencyInjection\Compiler\AddSimpleObjectHydrationModePass;
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
