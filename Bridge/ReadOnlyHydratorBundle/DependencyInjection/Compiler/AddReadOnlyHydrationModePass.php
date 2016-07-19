<?php

namespace steevanb\DoctrineReadOnlyHydrator\Bridge\ReadOnlyHydratorBundle\DependencyInjection\Compiler;

use steevanb\DoctrineReadOnlyHydrator\Hydrator\ReadOnlyHydrator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddReadOnlyHydrationModePass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $hydrator = [ReadOnlyHydrator::HYDRATOR_NAME, ReadOnlyHydrator::class];
        foreach ($container->getParameter('doctrine.entity_managers') as $name => $serviceName) {
            $definition = $container->getDefinition('doctrine.orm.' . $name . '_configuration');
            $definition->addMethodCall('addCustomHydrationMode', $hydrator);
        }
    }
}
