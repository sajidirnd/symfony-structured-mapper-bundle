<?php

namespace Euu\Bundle\StructuredMapperBundle\StructuredMapper\StructureDiscovery;

use Euu\StructuredMapper\Struct\Attribute\MapFrom;
use Euu\StructuredMapper\Struct\Attribute\MapTo;
use Euu\StructuredMapper\Struct\Attribute\OnMapFrom;
use Euu\StructuredMapper\Struct\Attribute\OnMapTo;
use Euu\StructuredMapper\Struct\Mapping;
use Euu\StructuredMapper\Struct\MapStruct;

class AttributeStructureDiscovery implements StructureDiscoveryInterface
{
    public function __construct(
        private readonly ?string $readDirectory = null,
        private readonly ?string $instanceOf = null,
    ) {
    }

    /**
     * @throws \ReflectionException
     */
    public function discoveryStructures(): array
    {
        return $this->parsePhpClasses($this->readDirectory, $this->instanceOf);
    }

    /**
     * @throws \ReflectionException
     */
    private function parsePhpClasses(?string $directory = null, ?string $instanceOf = null): array
    {
        $mapToStructs = [];
        $mapFromStructs = [];
        $allClasses = get_declared_classes();
        foreach ($allClasses as $class) {
            if ($instanceOf) {
                if (!is_subclass_of($class, $instanceOf)) {
                    continue;
                }
            }


            $reflection = new \ReflectionClass($class);
            if (!$reflection->isAbstract() && !$reflection->isInterface()) {
                $mapToStructs[] = $this->parseMapToStructures($reflection);
                $mapFromStructs[] = $this->parseMapFromStructures($reflection);
            }
        }

        return array_merge(
            ...$mapToStructs,
            ...$mapFromStructs
        );
    }

    /**
     * @return MapStruct[]
     */
    private function parseMapToStructures(\ReflectionClass $reflection): array
    {
        $from = $reflection->getName();

        $mappings = [];
        $mapStructs = [];

        foreach ($reflection->getProperties() as $property) {
            $onMapAttributes = $property->getAttributes(OnMapTo::class);

            foreach ($onMapAttributes as $attribute) {
                /** @var OnMapTo $onMap */
                $onMap = $attribute->newInstance();
                $to = $onMap->targetClass;
                $sourcePath = $property->getName();
                $targetPath = $onMap->targetPath ?? $property->getName();
                $mappings[$to][] = new Mapping(
                    $sourcePath,
                    $targetPath,
                    $onMap->transformerMeta,
                    $onMap->mappingContext
                );
            }
        }

        foreach ($reflection->getAttributes(MapTo::class) as $attribute) {
            /** @var MapTo $mapTo */
            $mapTo = $attribute->newInstance();
            $to = $mapTo->target;
            $mapStructs[] = new MapStruct(
                source: $from,
                target: $to,
                mapper: $mapTo->mapper,
                mappings: $mapTo->mappings,
                mapperContext: $mapTo->mapperContext
            );
        }

        foreach ($mapStructs as &$struct) {
            if (isset($mappings[$struct->target])) {
                $struct = new MapStruct(
                    source: $struct->source,
                    target: $struct->target,
                    mapper: $struct->mapper,
                    mappings: array_merge($struct->mappings, $mappings[$struct->target]),
                    mapperContext: $struct->mapperContext
                );

                unset($mappings[$struct->target]);
            }
        }

        foreach ($mappings as $to => $propertyMappings) {
            $mapStructs[] = new MapStruct(
                source: $from,
                target: $to,
                mappings: $propertyMappings
            );
        }

        return $mapStructs;
    }

    /**
     * @return MapStruct[]
     */
    private function parseMapFromStructures(\ReflectionClass $reflection): array
    {
        $to = $reflection->getName();

        $mappings = [];
        $mapStructs = [];

        foreach ($reflection->getProperties() as $property) {
            $onMapAttributes = $property->getAttributes(OnMapFrom::class);

            foreach ($onMapAttributes as $attribute) {
                /** @var OnMapFrom $onMap */
                $onMap = $attribute->newInstance();
                $from = $onMap->sourceClass;
                $sourcePath = $onMap->sourcePath ?? $property->getName();
                $targetPath = $property->getName();
                $mappings[$from][] = new Mapping(
                    $sourcePath,
                    $targetPath,
                    $onMap->transformerMeta,
                    $onMap->mappingContext
                );
            }
        }

        foreach ($reflection->getAttributes(MapFrom::class) as $attribute) {
            /** @var MapFrom $mapFrom */
            $mapFrom = $attribute->newInstance();
            $from = $mapFrom->source;
            $mapStructs[] = new MapStruct(
                source: $from,
                target: $to,
                mapper: $mapFrom->mapper,
                mappings: $mapFrom->mappings,
                mapperContext: $mapFrom->mapperContext
            );
        }

        foreach ($mapStructs as &$struct) {
            if (isset($mappings[$struct->source])) {
                $struct = new MapStruct(
                    source: $struct->source,
                    target: $struct->target,
                    mapper: $struct->mapper,
                    mappings: array_merge($struct->mappings, $mappings[$struct->source]),
                    mapperContext: $struct->mapperContext
                );

                unset($mappings[$struct->source]);
            }
        }

        foreach ($mappings as $from => $propertyMappings) {
            $mapStructs[] = new MapStruct(
                source: $from,
                target: $to,
                mappings: $propertyMappings
            );
        }

        return $mapStructs;
    }
}
