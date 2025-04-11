<?php

use Euu\StructuredMapper\StructureReader\LazyRegisteredStructure\StructureRegistry;
use Euu\Bundle\StructuredMapperBundle\StructuredMapper\Registry\CachedStructureRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $container, ContainerBuilder $builder) {

    $registryDefinition = new Definition();
    $registryDefinition->setLazy(true)
                        ->addTag('structured_mapper.registry');

    if ($builder->getParameter('structured_mapper.cache.enabled') === true) {
        $registryDefinition->setClass(CachedStructureRegistry::class);
        $registryDefinition->setArguments([
            '$cacheService' => new Reference($builder->getParameter('structured_mapper.cache.service')),
            '$cachePrefix' => $builder->getParameter('structured_mapper.cache.prefix'),
            '$cacheTtl' => $builder->getParameter('structured_mapper.cache.ttl'),
        ]);
    } else {
        $registryDefinition->setClass(StructureRegistry::class);
    }

    $builder->setDefinition('structured_mapper.registry.structures', $registryDefinition);
};
