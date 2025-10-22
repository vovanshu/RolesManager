<?php
namespace RolesManager\Service\Form;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use RolesManager\Form\RoleAddForm;

class RoleAddFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        return new RoleAddForm( $serviceLocator, $requestedName, $options);
    }
}
