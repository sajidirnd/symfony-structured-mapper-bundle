<?php

namespace Euu\Bundle\StructuredMapperBundle;

use Euu\Bundle\StructuredMapperBundle\DependencyInjection\Compiler\DoctrineEntityResolveTransformerPass;
use Euu\Bundle\StructuredMapperBundle\DependencyInjection\Compiler\MappersPass;
use Euu\Bundle\StructuredMapperBundle\DependencyInjection\Compiler\ObjectMapperPass;
use Euu\Bundle\StructuredMapperBundle\DependencyInjection\Compiler\StructureReadersPass;
use Euu\Bundle\StructuredMapperBundle\DependencyInjection\Compiler\ValueTransformersPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class StructuredMapperBundle extends AbstractBundle
{
    public const ALIAS = 'structured_mapper';

    protected string $extensionAlias = self::ALIAS;

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(BundleDirectory::CONFIG->getPath('/Definition.php'));
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $this->registerBundleParameters($this->extensionAlias, $config, $builder);

        $container->import(BundleDirectory::CONFIG->getPath('/services.yaml'));
    }

    private function registerBundleParameters(string $prefix, array $config, ContainerBuilder $builder): void
    {
        foreach ($config as $key => $value) {
            $paramName = $prefix . '.' . $key;
            if (is_array($value)) {
                $builder->setParameter($paramName, $value);
                $this->registerBundleParameters($paramName, $value, $builder);
            } else {
                $builder->setParameter($paramName, $value);
            }
        }
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new DoctrineEntityResolveTransformerPass());
        $container->addCompilerPass(new ValueTransformersPass());

        $container->addCompilerPass(new ObjectMapperPass());
        $container->addCompilerPass(new MappersPass());

        $container->addCompilerPass(new StructureReadersPass());
    }
}
