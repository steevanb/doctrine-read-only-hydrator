<?php

namespace steevanb\DoctrineReadOnlyHydrator\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use steevanb\DoctrineReadOnlyHydrator\Exception\ReadOnlyEntityCantBeFlushedException;
use steevanb\DoctrineReadOnlyHydrator\Exception\ReadOnlyEntityCantBePersistedException;
use steevanb\DoctrineReadOnlyHydrator\Hydrator\ReadOnlyHydrator;

class ReadOnlySubscriber implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(Events::prePersist, Events::preFlush);
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ReadOnlyEntityCantBePersistedException
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        if (isset($args->getObject()->{ReadOnlyHydrator::READ_ONLY_PROPERTY})) {
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
            if (isset($entity->{ReadOnlyHydrator::READ_ONLY_PROPERTY})) {
                throw new ReadOnlyEntityCantBeFlushedException($entity);
            }
        }
    }
}
