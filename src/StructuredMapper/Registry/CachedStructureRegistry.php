<?php

namespace Euu\Bundle\StructuredMapperBundle\StructuredMapper\Registry;

use Euu\StructuredMapper\Struct\MapStruct;
use Euu\StructuredMapper\StructureReader\LazyRegisteredStructure\StructureRegistry;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class CachedStructureRegistry extends StructureRegistry
{
    /*
     * todo
     *  normal registry burada decoratede edilebilirdi extends almak yerine bir versus et
     *  cache eklerken güvenilir bir normalizer içeri alınabilir MapStruct ı uygun formatta kayıt etmek için
     */

    /**
     * @param MapStruct[] $mapStructures
     */
    public function __construct(
        private readonly CacheItemPoolInterface $cacheService,
        private readonly string                 $cachePrefix,
        private readonly int                    $cacheTtl = 3600,
        array                                   $mapStructures = [],
    ) {
        parent::__construct($mapStructures);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addStructure(string $sourceClass, string $targetClass, MapStruct $mapStruct): void
    {
        $key = $this->getKey($sourceClass, $targetClass);

        $cacheItem = $this->cacheService->getItem($key);
        $cacheItem->expiresAfter($this->cacheTtl)
                    ->set($mapStruct);

        $this->cacheService->save($cacheItem);

        parent::addStructure($sourceClass, $targetClass, $mapStruct);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getStructure(string $sourceClass, string $targetClass): ?MapStruct
    {
        $key = $this->getKey($sourceClass, $targetClass);

        if (parent::hasStructure($sourceClass, $targetClass)) {
            return parent::getStructure($sourceClass, $targetClass);
        }

        return $this->cacheService->getItem($key)->get() ?? null;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function hasStructure(string $sourceClass, string $targetClass): bool
    {
        $key = $this->getKey($sourceClass, $targetClass);

        if (parent::hasStructure($sourceClass, $targetClass)) {
            return true;
        }

        return $this->cacheService->hasItem($key);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function removeStructure(string $sourceClass, string $targetClass): void
    {
        $key = $this->getKey($sourceClass, $targetClass);

        $this->cacheService->deleteItem($key);

        parent::removeStructure($sourceClass, $targetClass);
    }

    public function getKey(string $sourceClass, string $targetClass): string
    {
        $baseKey = parent::getKey($sourceClass, $targetClass);

        return $this->cachePrefix.crc32($baseKey);
    }

    public function clear(): void
    {
        $this->cacheService->clear();
    }
}
