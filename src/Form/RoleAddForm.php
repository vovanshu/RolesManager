<?php declare(strict_types=1);

namespace RolesManager\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Omeka\Permissions\Acl;
use RolesManager\Form\Element\RoleSelect;
use RolesManager\Form\Element\ParentRoleSelect;
use Generic\AbstractModule;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\EventManager\Event;
use RolesManager\Common;

class RoleAddForm extends Form
{
    use EventManagerAwareTrait;
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
            'name' => 'usingSelectedRole',
            'type' => 'radio',
            'options' => [
                'label' => 'Choose what Role Selector will be using', // @translate
                'value_options' => [
                    'parent' => 'Parent Role', // @translate
                    'template' => 'Role as Template', // @translate
                ],
            ],
            'attributes' => [
                'id' => 'usingSelectedRole',
                'value' => 'parent'
            ],
        ]);

        $this->add([
                'name' => 'o:parentRole',
                'type' => ParentRoleSelect::class,
                'attributes' => [
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select Role...', // @translate
                    'id' => 'parentRole'
                ],
                'options' => [
                    'label' => 'Parent Role', // @translate
                    'info' => 'Select Parent Role for create associated permissions and copy options.', // @translate
                    'empty_option' => '',
                    'name_as_value' => true,
                    'RoleCurrentUser' => $this->getRoleCurrentUser(),
                ],
            ]);

        $this->add([
                'name' => 'o:roleAStpl',
                'type' => RoleSelect::class,
                'attributes' => [
                    // 'value' => $userId ? $this->userSettings->get('default_item_sites', null, $userId) : [],
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select Role...', // @translate
                    // 'multiple' => true,
                    'id' => 'roleAStpl'
                ],
                'options' => [
                    'label' => 'Role as Template', // @translate
                    'info' => 'Select this if you want new role copy permissions.', // @translate
                    'empty_option' => '',
                    'name_as_value' => true,
                    'RoleCurrentUser' => $this->getRoleCurrentUser(),
                ],
            ]);
        $this->add([
                'name' => 'o:name',
                'type' => Element\Text::class,
                'options' => [
                    'label' => 'Name', // @translate
                ],
                'attributes' => [
                    'id' => 'name',
                    'required' => true,
                ],
            ]);
        $this->add([
                'name' => 'o:label',
                'type' => Element\Text::class,
                'options' => [
                    'label' => 'Label', // @translate
                ],
                'attributes' => [
                    'id' => 'label',
                    'required' => true
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


        $addEvent = new Event('form.add_elements', $this);
        $this->getEventManager()->triggerEvent($addEvent);

        $inputFilter = $this->getInputFilter();

        $inputFilter->add([
            'name' => 'o:roleAStpl',
            'allow_empty' => true,
        ]);

        $filterEvent = new Event('form.add_input_filters', $this, ['inputFilter' => $inputFilter]);
        $this->getEventManager()->triggerEvent($filterEvent);

    }

}
