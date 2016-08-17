<?php

namespace steevanb\DoctrineReadOnlyHydrator\Hydrator;

use Doctrine\ORM\Mapping\ClassMetadata;
use steevanb\DoctrineStats\Doctrine\ORM\Event\OverloadedHydratorTrait;

/**
 * Use it with https://github.com/steevanb/doctrine-stats
 */
class SimpleObjectHydrator extends \ComposerOverloadClass\steevanb\DoctrineReadOnlyHydrator\Hydrator\SimpleObjectHydrator
{
    use OverloadedHydratorTrait;

    /**
     * @param ClassMetadata $classMetaData
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    protected function createEntity(ClassMetadata $classMetaData, array $data)
    {
        $entity = parent::createEntity($classMetaData, $data);

        $this->dispatchPostCreateEntityEvent($classMetaData, $data);

        return $entity;
    }
}
