<?php declare(strict_types=1);

namespace RolesManager\Service\ControllerPlugin;

use RolesManager\Mvc\Controller\Plugin\CommonPlugin;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CommonPluginFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        return new CommonPlugin($serviceLocator, $requestedName, $options);
    }
}
