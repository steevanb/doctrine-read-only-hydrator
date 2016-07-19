<?php

namespace steevanb\DoctrineReadOnlyHydrator\Bridge\ReadOnlyHydratorBundle\DependencyInjection\Compiler;

use steevanb\DoctrineReadOnlyHydrator\Hydrator\SimpleObjectHydrator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddSimpleObjectHydrationModePass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $hydrator = [SimpleObjectHydrator::HYDRATOR_NAME, SimpleObjectHydrator::class];
        foreach ($container->getParameter('doctrine.entity_managers') as $name => $serviceName) {
            $definition = $container->getDefinition('doctrine.orm.' . $name . '_configuration');
            $definition->addMethodCall('addCustomHydrationMode', $hydrator);
        }
    }
}
