<?php
namespace RolesManager\Service;

use Interop\Container\ContainerInterface;
use RolesManager\Permissions\Acl;

/**
 * Access control list factory.
 */
class AclFactory extends \Omeka\Service\AclFactory
{

    /**
     * Create the access control list.
     *
     * @param ContainerInterface $serviceLocator
     * @return Acl
     */
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {

        $acl = new Acl;
        $acl->setServiceLocator($serviceLocator);

        $auth = $serviceLocator->get('Omeka\AuthenticationService');
        $acl->setAuthenticationService($auth);

        $this->addRoles($acl);
        $acl->registrationRoles();

        $this->addResources($acl, $serviceLocator);

        $status = $serviceLocator->get('Omeka\Status');
        if (!$status->isInstalled()
            || ($status->needsVersionUpdate() && $status->needsMigration())
        ) {
            // Allow all privileges during installation and migration.
            $acl->allow();
        } else {
            $this->addRules($acl);
        }

        return $acl;
        
    }

}
