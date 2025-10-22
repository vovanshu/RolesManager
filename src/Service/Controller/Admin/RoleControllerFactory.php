<?php
namespace RolesManager\Service\Controller\Admin;

use RolesManager\Controller\Admin\RoleController;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RoleControllerFactory implements FactoryInterface
{
    public function __invoke($serviceLocator, $requestedName, array $options = null)
    {
        $class = new RoleController();
        $class->setServiceLocator($serviceLocator);
        return $class;
    }
}
