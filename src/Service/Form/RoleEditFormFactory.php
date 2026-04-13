<?php
namespace RolesManager\Service\Form;

use RolesManager\Form\RoleEditForm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class RoleEditFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, ?array $options = null)
    {
        return new RoleEditForm( $serviceLocator, $requestedName, $options);
    }
}
