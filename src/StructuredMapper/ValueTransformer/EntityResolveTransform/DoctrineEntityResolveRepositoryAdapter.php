<?php

namespace Euu\Bundle\StructuredMapperBundle\StructuredMapper\ValueTransformer\EntityResolveTransform;

use Doctrine\ORM\EntityManagerInterface;
use Euu\StructuredMapper\ValueTransformer\EntityResolveTransform\Base\EntityResolveRepositoryAdapter;

class DoctrineEntityResolveRepositoryAdapter implements EntityResolveRepositoryAdapter
{
    public function __construct(private readonly EntityManagerInterface $doctrineEntityManager)
    {
    }

    public function getRepository(string $entityName): ?object
    {
        return $this->doctrineEntityManager->getRepository($entityName);
    }
}
