<?php
namespace RolesManager\Service\Controller\Admin;

use Interop\Container\ContainerInterface;
use RolesManager\Controller\Admin\IndexController;
use Laminas\ServiceManager\Factory\FactoryInterface;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $class = new IndexController();
        $class->setServiceLocator($services);
        return $class;
    }
}
