<?php
namespace RolesManager\Service\Form;

use RolesManager\Form\LoginForm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class LoginFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        return new LoginForm($serviceLocator, $requestedName, $options);
    }
}
