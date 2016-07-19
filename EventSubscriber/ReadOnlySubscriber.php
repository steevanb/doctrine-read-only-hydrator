<?php

namespace steevanb\DoctrineReadOnlyHydrator\EventSubscriber;

use steevanb\DoctrineReadOnlyHydrator\Hydrator\SimpleObjectHydrator;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use steevanb\DoctrineReadOnlyHydrator\Entity\ReadOnlyEntityInterface;
use steevanb\DoctrineReadOnlyHydrator\Exception\ReadOnlyEntityCantBeFlushedException;
use steevanb\DoctrineReadOnlyHydrator\Exception\ReadOnlyEntityCantBePersistedException;

class ReadOnlySubscriber implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::prePersist, Events::preFlush];
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ReadOnlyEntityCantBePersistedException
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        if ($this->isReadOnlyEntity($args->getObject())) {
            throw new ReadOnlyEntityCantBePersistedException($args->getObject());
        }
    }

    /**
     * @param PreFlushEventArgs $args
     * @throws ReadOnlyEntityCantBeFlushedException
     */
    public function preFlush(PreFlushEventArgs $args)
    {
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();
        $entities = array_merge(
            $unitOfWork->getScheduledEntityInsertions(),
            $unitOfWork->getScheduledEntityUpdates(),
            $unitOfWork->getScheduledEntityDeletions()
        );
        foreach ($entities as $entity) {
            if ($this->isReadOnlyEntity($entity)) {
                throw new ReadOnlyEntityCantBeFlushedException($entity);
            }
        }
    }

    /**
     * @param object $entity
     * @return bool
     */
    protected function isReadOnlyEntity($entity)
    {
        return
            $entity instanceof ReadOnlyEntityInterface
            || isset($entity->{SimpleObjectHydrator::READ_ONLY_PROPERTY});
    }
}
