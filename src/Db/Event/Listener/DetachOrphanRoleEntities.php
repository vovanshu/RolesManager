<?php declare(strict_types=1);
namespace RolesManager\Db\Event\Listener;

use Doctrine\ORM\Event\PreFlushEventArgs;
use RolesManager\Entity\RoleResource;
use RolesManager\Entity\RoleUser;

/**
 * Automatically detach roles that reference unknown resources or users.
 *
 * It allows to avoid issues during batch creation, when resources are detached.
 */
class DetachOrphanRoleEntities
{
    /**
     * Detach all RoleEntities that reference Entities not currently in the
     * entity manager.
     *
     * @param PreFlushEventArgs $event
     */
    public function preFlush(PreFlushEventArgs $event): void
    {
        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        $identityMap = $uow->getIdentityMap();

        if (isset($identityMap[RoleResource::class])) {
            foreach ($identityMap[RoleResource::class] as $roleResource) {
                $resource = $roleResource->getResource();
                if ($resource && !$em->contains($resource)) {
                    $em->detach($roleResource);
                }
            }
        }

        if (isset($identityMap[RoleUser::class])) {
            foreach ($identityMap[RoleUser::class] as $roleUser) {
                $user = $roleUser->getUser();
                if ($user && !$em->contains($user)) {
                    $em->detach($roleUser);
                }
            }
        }
    }
}
