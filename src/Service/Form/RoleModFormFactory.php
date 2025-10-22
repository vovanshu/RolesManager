<?php
namespace RolesManager\Service\Form;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use RolesManager\Form\RoleModForm;

class RoleModFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        return new RoleModForm( $serviceLocator, $requestedName, $options);
    }
}
