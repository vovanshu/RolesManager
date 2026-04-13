<?php declare(strict_types=1);

namespace RolesManager\Service\ControllerPlugin;

use RolesManager\Mvc\Controller\Plugin\GeneralPlugin;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GeneralPluginFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $serviceLocator, $requestedName, ?array $options = null)
    {
        return new GeneralPlugin($serviceLocator, $requestedName, $options);
    }
}
