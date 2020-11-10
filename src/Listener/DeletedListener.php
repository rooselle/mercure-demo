<?php

namespace App\Listener;

use Doctrine\ORM\Event\OnFlushEventArgs;

class DeletedListener
{
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (
                property_exists($entity, 'deletedAt')
                && !$entity->deletedAt instanceof \Datetime
            ) {
                $entity->deletedAt = new \DateTime();

                try {
                    $em->persist($entity);
                    $uow->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($entity)), $entity);
                } catch (\Exception $exception) {
                    continue;
                }
            }
        }
    }
}
