<?php declare(strict_types=1);

namespace RolesManager\Form;

// use Laminas\Form\Element;
use Laminas\Form\Form;
// use Omeka\Permissions\Acl;
// use RolesManager\Form\Element\RoleSelect;
// use Generic\AbstractModule;
// use Interop\Container\ContainerInterface;
use RolesManager\Common;

class RoleModForm extends Form
{

    use Common;

    public function __construct($serviceLocator, $requestedName, $options)
    {
        $this->setServiceLocator($serviceLocator);
        parent::__construct(Null, []);
    }

    public function init(): void
    {

        $this->setAttribute('id', 'role-form');
        $this->add([
                'name' => 'o:name',
                'type' => 'Select',
                'attributes' => [
                    // 'value' => $userId ? $this->userSettings->get('default_item_sites', null, $userId) : [],
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select role for edit permissions', // @translate
                    // 'multiple' => true,
                    'id' => 'name',
                    'required' => true
                ],
                'options' => [
                    'label' => 'Role', // @translate
                    'info' => 'Select role for modification his options and permissions.', // @translate
                    'value_options' => $this->getNativeRolesForMod(),
                    'empty_option' => '',
                ],
            ]);
        $this->add([
                'name' => 'o:label',
                'type' => 'text',
                'options' => [
                    'label' => 'Label', // @translate
                ],
                'attributes' => [
                    'id' => 'label',
                    'required' => false
                ],
            ]);
        $this->add([
                'name' => 'o:active',
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Active', // @translate
                    'info' => 'Select this if you want new role to be active.', // @translate
                ],
                'attributes' => [
                    'id' => 'active',
                    'value' => true,
                ],
            ]);

    }

}
