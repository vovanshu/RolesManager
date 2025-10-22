<?php declare(strict_types=1);

namespace RolesManager\Service\Form\Element;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use RolesManager\Form\Element\ParentRoleSelect;

class ParentRoleSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new ParentRoleSelect(null, $options ?? []);
        return $element
            ->setApiManager($services->get('Omeka\ApiManager'))
            ->setUrlHelper($services->get('ViewHelperManager')->get('Url'));
    }
}
