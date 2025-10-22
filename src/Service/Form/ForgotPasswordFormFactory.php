<?php
namespace RolesManager\Service\Form;

use RolesManager\Form\ForgotPasswordForm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ForgotPasswordFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        return new ForgotPasswordForm($serviceLocator, $requestedName, $options);
    }
}
