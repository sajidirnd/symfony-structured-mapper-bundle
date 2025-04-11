<?php

namespace Euu\Bundle\StructuredMapperBundle\DependencyInjection\Compiler;

use Euu\StructuredMapper\Mapper\ObjectMapper\ObjectMapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ObjectMapperPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $objectMapperDefinition = new Definition();
        $objectMapperDefinition
            ->setClass(ObjectMapper::class)
            ->addTag('structured_mapper.mapper')
            ->setLazy(true)
            ->setArguments([
                '$transformerRegistry' => new Reference('structured_mapper.registry.value_transformers'),
                '$propertyInfoExtractor' => $this->initPropertyInfoExtractor($container),
                '$propertyAccessor' => $this->initPropertyAccessor($container),
            ]);

        $container->setDefinition('structured_mapper.mapper.object_mapper', $objectMapperDefinition);
    }

    private function initPropertyInfoExtractor(ContainerBuilder $container): Reference
    {
        if (!$container->has('property_info')) {
            throw new \LogicException('The "property_info" service is required but not available. Did you forget to install symfony/property-info?');
        }

        return new Reference('property_info');
    }

    private function initPropertyAccessor(ContainerBuilder $container): Reference
    {
        if (!$container->has('property_accessor')) {
            throw new \LogicException('The "property_accessor" service is required but not available. Did you forget to install symfony/property-access?');
        }

        return new Reference('property_accessor');
    }
}
