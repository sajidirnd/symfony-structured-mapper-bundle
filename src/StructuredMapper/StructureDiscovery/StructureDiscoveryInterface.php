<?php

namespace Euu\Bundle\StructuredMapperBundle\StructuredMapper\StructureDiscovery;

use Euu\StructuredMapper\Struct\MapStruct;

interface StructureDiscoveryInterface
{
    /**
     * @return MapStruct[]
     */
    public function discoveryStructures(): array;
}
