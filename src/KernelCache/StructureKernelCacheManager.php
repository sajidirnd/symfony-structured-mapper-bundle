<?php

namespace Euu\Bundle\StructuredMapperBundle\KernelCache;

use Euu\StructuredMapper\Struct\MapStruct;
use Euu\StructuredMapper\StructureReader\LazyRegisteredStructure\StructureRegistry;
use Euu\Bundle\StructuredMapperBundle\StructuredMapper\StructureDiscovery\StructureDiscoveryInterface;
use Euu\Bundle\StructuredMapperBundle\StructuredMapper\Registry\CachedStructureRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class StructureKernelCacheManager implements CacheWarmerInterface, CacheClearerInterface
{
    public function __construct(
        private readonly CachedStructureRegistry|StructureRegistry $cachedStructureRegistry,
        private readonly iterable                                  $readers = [],
        private readonly bool                                      $enabled = false
    ) {
    }

    public function isOptional(): bool
    {
        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        if (!$this->enabled) {
            return [];
        }

        /** @var MapStruct[] $structures */
        $structures = [];

        foreach ($this->readers as $reader) {
            if (!$reader instanceof StructureDiscoveryInterface) {
                continue;
            }

            array_push($structures, ...$reader->discoveryStructures());
        }

        foreach ($structures as $structure) {
            $this->cachedStructureRegistry->addStructure($structure->source, $structure->target, $structure);
        }

        return [];
    }

    public function clear(string $cacheDir): void
    {
        $this->cachedStructureRegistry->clear();
    }
}
