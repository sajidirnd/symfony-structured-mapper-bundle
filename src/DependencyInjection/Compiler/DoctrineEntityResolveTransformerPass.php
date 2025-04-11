<?php

namespace Euu\Bundle\StructuredMapperBundle\DependencyInjection\Compiler;

use Euu\Bundle\StructuredMapperBundle\StructuredMapper\ValueTransformer\EntityResolveTransform\DoctrineEntityResolveRepositoryAdapter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineEntityResolveTransformerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $defaultEntityManager = new Reference('doctrine.orm.default_entity_manager');

        $doctrineRepositoryAdapterDefinition = new Definition();
        $doctrineRepositoryAdapterDefinition
            ->setClass(DoctrineEntityResolveRepositoryAdapter::class)
            ->setLazy(true)
            ->addArgument($defaultEntityManager);

        $parentId = 'structured_mapper.value_transformer.abstract_entity_resolve_transformer';
        $parent = $container->getDefinition($parentId);

        $doctrineEntityResolveTransformer = new Definition();
        $doctrineEntityResolveTransformer
            ->setClass($parent->getClass())
            ->setLazy($parent->isLazy())
            ->setTags($parent->getTags())
            ->addArgument($doctrineRepositoryAdapterDefinition);

        $container->setDefinition(
            'structured_mapper.value_transformer.doctrine_entity_resolve_transformer',
            $doctrineEntityResolveTransformer
        );
    }
}
