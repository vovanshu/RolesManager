<?php declare(strict_types=1);

namespace RolesManager\Service\Form\Element;

use RolesManager\Form\Element\RoleSelect;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RoleSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new RoleSelect(null, $options ?? []);
        return $element
            ->setApiManager($services->get('Omeka\ApiManager'))
            ->setUrlHelper($services->get('ViewHelperManager')->get('Url'));
    }
}
