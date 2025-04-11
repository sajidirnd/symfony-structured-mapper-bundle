<?php

namespace Euu\Bundle\StructuredMapperBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class StructureReadersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $structureReaderRegistry = $container->getDefinition('structured_mapper.registry.structure_readers');

        $structureReaders = $container->findTaggedServiceIds('structured_mapper.structure_reader');
        foreach ($structureReaders as $id => $tags) {
            $readerDef = $container->getDefinition($id);
            if ($readerDef->isAbstract()) {
                continue;
            }

            $readerRef = new Reference($id);
            $structureReaderRegistry->addMethodCall('set', [$readerDef->getClass(), $readerRef]);
        }
    }
}
