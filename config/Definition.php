<?php

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    $rootNode = $definition->rootNode();
     $rootNode
            ->children()
                ->arrayNode('default_mapper_context')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                    ->defaultValue([]) // Boş default
                ->end()
                ->arrayNode('default_mapping_context')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                    ->defaultValue([]) // Boş default
                ->end()
                ->arrayNode('default_transformer_context')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                    ->defaultValue([]) // Boş default
                ->end()
            ->end();

    $cacheNode = $rootNode->children()->arrayNode('cache');
    $cacheNode
        ->addDefaultsIfNotSet()
        ->children()
            ->booleanNode('enabled')->defaultFalse()->end()
            ->integerNode('ttl')->defaultValue(3600)->end()
            ->scalarNode('prefix')->defaultValue('structured_mapper')->end()
            ->scalarNode('service')->defaultValue('cache.app')->end()

            ->arrayNode('preload')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->arrayNode('readers')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('attribute_reader')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('instance_of')->defaultNull()->end()
                                    ->scalarNode('read_directory')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->arrayNode('yaml_reader')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('read_directory')->defaultNull()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->validate()
                    ->ifTrue(function ($config) {
                        return $config['enabled']
                            && empty($config['readers']['attribute_reader'])
                            && empty($config['readers']['yaml_reader']);
                    })
                    ->thenInvalid('You must configure at least one reader when preload is enabled.')
                ->end()
            ->end() // preload
        ->end() // cache children
    ->end(); // cacheNode
};
