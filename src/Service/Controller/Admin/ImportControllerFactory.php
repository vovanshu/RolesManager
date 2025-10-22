<?php
namespace RolesManager\Service\Controller\Admin;

use Laminas\ServiceManager\Factory\FactoryInterface;
use RolesManager\Controller\Admin\ImportController;

class ImportControllerFactory implements FactoryInterface
{
    public function __invoke($services, $requestedName, array $options = null)
    {
        return new ImportController($services, $requestedName, $options);
    }
}
