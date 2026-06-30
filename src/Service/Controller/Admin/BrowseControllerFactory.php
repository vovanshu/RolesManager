<?php
namespace RolesManager\Service\Controller\Admin;

use RolesManager\Controller\Admin\BrowseController;
use Laminas\ServiceManager\Factory\FactoryInterface;

class BrowseControllerFactory implements FactoryInterface
{
    public function __invoke($serviceLocator, $requestedName, ?array $options = null)
    {
        $class = new BrowseController();
        $class->setServiceLocator($serviceLocator);
        return $class;
    }
}
