<?php
namespace RolesManager\Service\Form\Element;

use Interop\Container\ContainerInterface;
use Laminas\Form\Element\Select;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RoleNativeSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $acl = $services->get('Omeka\Acl');

        // $EntityManager = $services->get('Omeka\EntityManager');
        $ApiManager = $services->get('Omeka\ApiManager');

        $RS = $ApiManager->search('roles', [])->getContent();

        $explude[] = $acl::ROLE_GLOBAL_ADMIN;
        // $explude[] = $acl::ROLE_SITE_ADMIN;
        if(!empty($RS)){
            foreach($RS as $role){
                $explude[] = $role->name();
            }
        }
        $rolesAll = $acl->getRoleLabels();

        foreach($rolesAll as $name => $label){
            if(!in_array($name, $explude)){
                $roles[$name] = $label;
            }
        }

        $element = new Select;
        $element->setValueOptions($roles);
        $element->setEmptyOption('Select role…'); // @translate
        return $element;
    }
}
