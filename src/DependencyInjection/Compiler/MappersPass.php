<?php

namespace Euu\Bundle\StructuredMapperBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MappersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $mapperRegistry = $container->getDefinition('structured_mapper.registry.mappers');

        $mappers = $container->findTaggedServiceIds('structured_mapper.mapper');
        foreach ($mappers as $id => $tags) {
            $mapperDef = $container->getDefinition($id);
            if ($mapperDef->isAbstract()) {
                continue;
            }

            $mapperRef = new Reference($id);
            $mapperRegistry->addMethodCall('set', [$mapperDef->getClass(), $mapperRef]);
        }
    }
}
