<?php

namespace Euu\Bundle\StructuredMapperBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ValueTransformersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $valueTransformerRegistry = $container->getDefinition('structured_mapper.registry.value_transformers');

        $valueTransformers = $container->findTaggedServiceIds('structured_mapper.value_transformer');
        foreach ($valueTransformers as $id => $tags) {
            $transformerDef = $container->getDefinition($id);
            if ($transformerDef->isAbstract()) {
                continue;
            }

            $transformerRef = new Reference($id);
            $valueTransformerRegistry->addMethodCall('set', [$transformerDef->getClass(), $transformerRef]);
        }
    }
}
