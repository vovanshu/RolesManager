<?php declare(strict_types=1);

namespace RolesManager\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\EventManager\Event;
// use Omeka\Api\Manager as ApiManager;
use Omeka\Permissions\Acl;
use Omeka\Form\Element\ItemSetSelect;
use Omeka\Form\Element\ResourceSelect;
use Omeka\Form\Element\SiteSelect;
// use Omeka\Settings;
// use Interop\Container\ContainerInterface;
// use Generic\AbstractModule;
use RolesManager\Form\Element\ParentRoleSelect;
use RolesManager\Common;

class RoleEditForm extends Form
{
    use EventManagerAwareTrait;
    use Common;

    protected $allow_empty;

    protected $imitation_fields = [
        'o:showonlyallowed' => 'Show only allowed', // @translate 
        'o:allowviewallitems' => 'Allow view all Items', // @translate
        'o:allowviewallmedias' => 'Allow view all Medias', // @translate
        'o:allowviewallitemsets' => 'Allow view all Item Sets', // @translate
        'o:allowviewallassets' => 'Allow view all Assets', // @translate
        'o:allowed_resource_template' => 'Allowed resource template', // @translate
        'o:allowed_item_sets' => 'Allowed item sets for items', // @translate
        'o:allowed_item_sites' => 'Allowed sites for items', // @translate
        'o:hide_apikey' => 'Hide Api KEYs', // @translate
        'o:hide_default_resource_template' => 'Hide default resource template selector', // @translate
        'o:list_partials_advancedsearch' => 'List partials Advanced Search', // @translate
        'o:list_disallowed_partials_advancedsearch' => 'List disallowed partials Advanced Search', // @translate
        'o:hide_site_selector' => 'Hide site selector on pages', // @translate
        'o:withoutowner_site_selector' => 'List site selector without owner', // @translate
        'o:remove_browse_defaults_admin_sites' => 'Remove Site browse default sort', // @translate
        'o:remove_columns_admin_sites' => 'Remove Site browse columns', // @translate
        'o:hide_item_sets_select' => 'Hide Item Set select on pages', // @translate
        'o:withoutowner_item_set_selector' => 'List item set selector without owner', // @translate
        'o:remove_browse_defaults_admin_item_sets' => 'Remove Item Set browse default sort', // @translate
        'o:remove_columns_admin_item_sets' => 'Remove Item Set browse columns', // @translate
        'o:remove_columns_admin_items' => 'Remove Item browse columns', // @translate
        'o:remove_browse_defaults_admin_items' => 'Remove Item browse defaults', // @translate
        'o:hide_items_advanced_settings' => 'Hide advanced settings Item', // @translate
        'o:remove_columns_admin_media' => 'Remove Media browse columns', // @translate
        'o:remove_browse_defaults_admin_media' => 'Remove Media browse defaults', // @translate
        'o:list_media_types' => 'List media types', // @translate
        'o:list_disallowed_media_types' => 'List disallowed media types', // @translate
    ];

    protected $partials_AdvancedSearch = [
        'common/advanced-search/sort' => 'Sort', // @translate
        'common/advanced-search/fulltext' => 'Fulltext', // @translate
        'common/advanced-search/properties' => 'Properties', // @translate
        'common/advanced-search/resource-class' => 'Resource class', // @translate
        'common/advanced-search/resource-template' => 'Resource template', // @translate
        'common/advanced-search/item-sets' => 'Item sets', // @translate
        'common/advanced-search/site' => 'Site', // @translate
        'common/advanced-search/has-media' => 'Has media', // @translate
        'common/advanced-search/owner' => 'Owner', // @translate
        'common/advanced-search/visibility' => 'Visibility', // @translate
        'common/advanced-search/ids' => 'Ids', // @translate
        'common/advanced-search/media_ingester' => 'Media ingester', // @translate
        'common/advanced-search/data-type-geography' => 'data-type-geography', // @translate
        'common/numeric-data-types-advanced-search' => 'numeric-data-types-advanced-search', // @translate
    ];

    public function __construct($serviceLocator, $requestedName, $options)
    {
        $this->setServiceLocator($serviceLocator);
        parent::__construct(Null, $options);
    }

    public function init(): void
    {

        $this->add([
            'name' => 'role',
            'type' => 'fieldset',
        ]);
        $this->add([
            'name' => 'options',
            'type' => 'fieldset',
        ]);
        if(empty($this->options['parent'])){
            $this->add([
                'name' => 'permissions',
                'type' => 'fieldset',
            ]);
        }

        $this->setAttribute('id', 'role-form');

        $this->add([
            'type' => 'csrf',
            'name' => 'csrf',
            'options' => [
                'label' => 'CSRF',
                'csrf_options' => [
                    'timeout' => 3600, // 1 hour
                ],
            ],
        ]);

        $this->get('role')->add([
            'name' => 'o:name',
            'type' => 'text',
            'options' => [
                'label' => 'Name', // @translate
            ],
            'attributes' => [
                'id' => 'name',
                'required' => false,
                'disabled' => true
            ],
        ]);

        $this->get('role')->add([
            'name' => 'o:label',
            'type' => 'text',
            'options' => [
                'label' => 'Label', // @translate
            ],
            'attributes' => [
                'id' => 'label',
                'required' => true
            ],
        ]);

        if(!$this->isParentRole($this->options['current']) && !empty($this->options['created'])){
            $this->get('role')->add([
                'name' => 'o:parent',
                'type' => ParentRoleSelect::class,
                'attributes' => [
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select Role...', // @translate
                    'id' => 'parent'
                ],
                'options' => [
                    'label' => 'Parent Role', // @translate
                    'empty_option' => '',
                    'name_as_value' => true,
                    'current' => $this->options['current'],
                    'RoleCurrentUser' => $this->getRoleCurrentUser(),
                ],
            ]);
        }
        
        if($this->isParentRole($this->options['current']) && !empty($this->options['created'])){
            $this->get('role')->add([
                'name' => 'o:imitation_fields',
                'type' => 'select',
                'attributes' => [
                    'multiple' => true,
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select imitation fields...', // @translate
                    'id' => 'imitation_fields'
                ],
                'options' => [
                    'label' => 'Imitation fields', // @translate
                    'value_options' => $this->imitation_fields,
                    'empty_option' => '',
                ],
            ]);
            $this->allow_empty[]['role'] = 'o:imitation_fields';
        }

        $this->get('role')->add([
            'name' => 'o:active',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Active', // @translate
                'info' => 'Select this if you want new role to be active.', // @translate
            ],
            'attributes' => [
                'id' => 'active'
            ],
        ]);

        $this->get('role')->add([
            'name' => 'o:created',
            'type' => 'hidden',
            'options' => [
                'label' => 'Active', // @translate
            ],
            'attributes' => [
                'id' => 'created'
            ],
        ]);

        $optionsFieldset = $this->getFormOptions();
        
        if(!empty($this->options['parent'])){
            $imitation_fields = $this->getRoleOps($this->options['parent'], 'o:imitation_fields');
            foreach($imitation_fields as $key_field){
                // $value_field = $this->getRoleOps($this->options['parent'], $key_field);
                $optionsFieldset->get($key_field)->setAttribute('disabled', 'disabled');
                $this->allow_empty[]['options'] = $key_field;
                // echo $key_field.' => '.$value_field.'<br>';
            }
        }

        $this->getFormPermissions();

        $addEvent = new Event('form.add_elements', $this);
        $this->getEventManager()->triggerEvent($addEvent);

        // separate input filter stuff so that the event work right
        $inputFilter = $this->getInputFilter();

        if(isset($this->options['allowDisactive']) && $this->options['allowDisactive'] === False){
            $this->get('role')->get('o:active')->setAttribute('disabled', 'disabled');
            $inputFilter->get('role')->add([
                'name' => 'o:active',
                'allow_empty' => true,
            ]);
        }

        $inputFilter->get('options')->add([
            'name' => 'o:allowed_item_sets',
            'allow_empty' => true,
        ]);
        $inputFilter->get('options')->add([
            'name' => 'o:allowed_item_sites',
            'allow_empty' => true,
        ]);
        $inputFilter->get('options')->add([
            'name' => 'o:list_media_types',
            'allow_empty' => true,
        ]);

        if(!empty($this->allow_empty)){
            foreach($this->allow_empty as $allowed_empty){
                foreach($allowed_empty as $group => $name){
                    $inputFilter->get($group)->add([
                        'name' => $name,
                        'allow_empty' => true,
                    ]);
                }
            }
        }

        // if(!empty($permission_element_groups)){
        //     foreach($permission_element_groups as $pegk => $pegn){
        //         $inputFilter->get('permissions')->add([
        //             'name' => $pegk.'-all',
        //             'allow_empty' => true,
        //         ]);
        //     }
        // }

        // if(!empty($permission_element)){
        //     foreach($permission_element as $element => $value){
        //         $inputFilter->get('permissions')->add([
        //             'name' => $element.'-state',
        //             'allow_empty' => true,
        //         ]);
        //     }
        // }

        $filterEvent = new Event('form.add_input_filters', $this, ['inputFilter' => $inputFilter]);
        $this->getEventManager()->triggerEvent($filterEvent);

    }

    private function isAllowedSet($class, $resource, $privileges)
    {

        if($this->getRoleCurrentUser() == Acl::ROLE_GLOBAL_ADMIN){
            return True;
        }
        if(in_array($class, $this->getConf('AllowSetRulesOnlyGlobalAdmin'))){
            return False;
        }

        foreach($privileges as $k => $v){
            if(is_array($v)){
                $priv = $k;
            }else{
                $priv = $v;
            }
            if(!$this->getAcl()->userIsAllowed($resource, $priv)){
                return False;
            }
        }
        return True;

    }

    private function getFormOptions()
    {


        $optionsFieldset = $this->get('options');
        $optionsFieldset->setOption('element_groups', [
            'options' => 'Options',
            'advancedsearch' => 'Advanced Search',
            'site' => 'Site',
            'itemSet' => 'Item Set',
            'items' => 'Items',
            'media' => 'Media',
            'information' => 'Addition information'
        ]);

        $optionsFieldset->add([
            'name' => 'o:showonlyallowed',
            'type' => 'checkbox',
            'options' => [
                'element_group' => 'options',
                'label' => 'Show only allowed', // @translate
            ],
            'attributes' => [
                'id' => 'showonlyallowed'
            ],
        ]);

        $optionsFieldset->add([
            'name' => 'o:allowviewallitems',
            'type' => 'checkbox',
            'options' => [
                'element_group' => 'options',
                'label' => 'Allow view all Items', // @translate
            ],
            'attributes' => [
                'id' => 'allowviewallitems'
            ],
        ]);
        
        $optionsFieldset->add([
            'name' => 'o:allowviewallmedias',
            'type' => 'checkbox',
            'options' => [
                'element_group' => 'options',
                'label' => 'Allow view all Medias', // @translate
            ],
            'attributes' => [
                'id' => 'allowviewallmedias'
            ],
        ]);

        $optionsFieldset->add([
            'name' => 'o:allowviewallitemsets',
            'type' => 'checkbox',
            'options' => [
                'element_group' => 'options',
                'label' => 'Allow view all Item Sets', // @translate
            ],
            'attributes' => [
                'id' => 'allowviewallitemsets'
            ],
        ]);

        $optionsFieldset->add([
            'name' => 'o:allowviewallassets',
            'type' => 'checkbox',
            'options' => [
                'element_group' => 'options',
                'label' => 'Allow view all Assets', // @translate
            ],
            'attributes' => [
                'id' => 'allowviewallassets'
            ],
        ]);

        $optionsFieldset->add([
            'name' => 'o:allowed_resource_template',
            'type' => ResourceSelect::class,
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select a template', // @translate
                'multiple' => true,
                'id' => 'allowed_resource_template',
            ],
            'options' => [
                'element_group' => 'options',
                'label' => 'Allowed resource template', // @translate
                'empty_option' => '',
                'resource_value_options' => [
                    'resource' => 'resource_templates',
                    'query' => [
                        'sort_by' => 'label',
                    ],
                    'option_text_callback' => function ($resourceTemplate) {
                        return $resourceTemplate->label();
                    },
                ],
            ],
        ]);

        $this->allow_empty[]['options'] = 'o:allowed_resource_template';

        $optionsFieldset->add([
            'name' => 'o:allowed_item_sets',
            'type' => ItemSetSelect::class,
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select item sets', // @translate
                'multiple' => true,
                'id' => 'allowed_item_sets',
            ],
            'options' => [
                'element_group' => 'options',
                'label' => 'Allowed item sets for items', // @translate
                'empty_option' => '',
                'query' => ['is_open' => true],
            ],
        ]);

        $optionsFieldset->add([
            'name' => 'o:allowed_item_sites',
            'type' => SiteSelect::class,
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select sites', // @translate
                'multiple' => true,
                'id' => 'allowed_item_sites',
            ],
            'options' => [
                'element_group' => 'options',
                'label' => 'Allowed sites for items', // @translate
                'empty_option' => '',
            ],
        ]);

        $optionsFieldset->add([
            'name' => 'o:hide_apikey',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Hide Api KEYs', // @translate
                'info' => 'If need hide Api KEYs on page edit user.', // @translate
                'element_group' => 'options'
            ],
            'attributes' => [
                'id' => 'hide_apikey'
            ]
        ]);

        $optionsFieldset->add([
            'name' => 'o:hide_default_resource_template',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Hide default resource template selector', // @translate
                'info' => 'If need hide default resource template selector on page user edit.', // @translate
                'element_group' => 'options'
            ],
            'attributes' => [
                'id' => 'hide_default_resource_template'
            ]
        ]);

        // 'element_group' => 'advancedsearch',

        $optionsFieldset->add([
            'name' => 'o:list_partials_advancedsearch',
            'type' => 'Select',
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select partials Advanced Search', // @translate
                'multiple' => true,
                'id' => 'list_partials_advancedsearch',
            ],
            'options' => [
                'element_group' => 'advancedsearch',
                'label' => 'List partials Advanced Search', // @translate
                'empty_option' => '', // @translate
                'value_options' => $this->partials_AdvancedSearch,
                'info' => ''
            ],
        ]);
        
        $this->allow_empty[]['options'] = 'o:list_partials_advancedsearch';

        $optionsFieldset->add([
            'name' => 'o:list_disallowed_partials_advancedsearch',
            'type' => 'checkbox',
            'options' => [
                'label' => 'List disallowed partials Advanced Search', // @translate
                'info' => 'If checked List partials Advanced Search is list disallowed elements', // @translate
                'element_group' => 'advancedsearch'
            ],
            'attributes' => [
                'id' => 'list_disallowed_partials_advancedsearch'
            ]
        ]);

        $this->allow_empty[]['options'] = 'o:list_disallowed_partials_advancedsearch';

        // 'element_group' => 'site',

        $optionsFieldset->add([
            'name' => 'o:hide_site_selector',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Hide site selector on pages', // @translate
                'info' => 'If need hide Site selector on pages create/edit items, users.', // @translate
                'element_group' => 'site'
            ],
            'attributes' => [
                'id' => 'hide_site_selector'
            ]
        ]);

        $optionsFieldset->add([
            'name' => 'o:withoutowner_site_selector',
            'type' => 'checkbox',
            'options' => [
                'label' => 'List site selector without owner', // @translate
                'element_group' => 'site'
            ],
            'attributes' => [
                'id' => 'withoutowner_site_selector',
                'disabled' => !empty($this->getSets('withoutowner_site_selector')) ? 'disabled' : False,
                'value' => !empty($this->getSets('withoutowner_site_selector')) ? 1 : 0
            ]
        ]);
        $this->allow_empty[]['options'] = 'o:withoutowner_site_selector';

        $optionsFieldset->add([
            'name' => 'o:remove_browse_defaults_admin_sites',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Remove Site browse default sort', // @translate
                'info' => 'If need remove Site browse default sort on page edit user.', // @translate
                'element_group' => 'site'
            ],
            'attributes' => [
                'id' => 'remove_browse_defaults_admin_sites'
            ]
        ]);

        $optionsFieldset->add([
            'name' => 'o:remove_columns_admin_sites',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Remove Site browse columns', // @translate
                'info' => 'If need remove Site browse columns on page edit user.', // @translate
                'element_group' => 'site'
            ],
            'attributes' => [
                'id' => 'remove_columns_admin_sites'
            ]
        ]);

        $optionsFieldset->add([
            'name' => 'o:hide_item_sets_select',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Hide Item Set select on pages', // @translate
                'info' => 'If need hide Item Set select on pages create/edit items, users.', // @translate
                'element_group' => 'itemSet'
            ],
            'attributes' => [
                'id' => 'hide_item_sets_select'
            ]
        ]);

        $optionsFieldset->add([
            'name' => 'o:withoutowner_item_set_selector',
            'type' => 'checkbox',
            'options' => [
                'label' => 'List item set selector without owner', // @translate
                'element_group' => 'itemSet'
            ],
            'attributes' => [
                'id' => 'withoutowner_item_set_selector',
                'disabled' => !empty($this->getSets('withoutowner_item_set_selector')) ? 'disabled' : False,
                'value' => !empty($this->getSets('withoutowner_item_set_selector')) ? 1 : 0
            ]
        ]);
        $this->allow_empty[]['options'] = 'o:withoutowner_item_set_selector';

        $optionsFieldset->add([
            'name' => 'o:remove_browse_defaults_admin_item_sets',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Remove Item Set browse default sort', // @translate
                'info' => 'If need remove Item Set browse default sort on page edit user.', // @translate
                'element_group' => 'itemSet'
            ],
            'attributes' => [
                'id' => 'remove_browse_defaults_admin_item_sets'
            ]
        ]);

        $optionsFieldset->add([
            'name' => 'o:remove_columns_admin_item_sets',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Remove Item Set browse columns', // @translate
                'info' => 'If need remove Item Set browse columns on page edit user.', // @translate
                'element_group' => 'itemSet'
            ],
            'attributes' => [
                'id' => 'remove_columns_admin_item_sets'
            ]
        ]);

        $optionsFieldset->add([
            'name' => 'o:remove_columns_admin_items',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Remove Item browse columns', // @translate
                'info' => 'If need remove Item browse columns on page edit user.', // @translate
                'element_group' => 'items'
            ],
            'attributes' => [
                'id' => 'remove_columns_admin_items'
            ]
        ]);

        $optionsFieldset->add([
            'name' => 'o:remove_browse_defaults_admin_items',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Remove Item browse defaults', // @translate
                'info' => 'If need remove Item browse defaults on page edit user.', // @translate
                'element_group' => 'items'
            ],
            'attributes' => [
                'id' => 'remove_browse_defaults_admin_items'
            ]
        ]);

        $optionsFieldset->add([
            'name' => 'o:hide_items_advanced_settings',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Hide advanced settings Item', // @translate
                'info' => 'If need hide advanced settings on page add/edit Items.', // @translate
                'element_group' => 'items'
            ],
            'attributes' => [
                'id' => 'hide_items_advanced_settings'
            ]
        ]);

        $optionsFieldset->add([
            'name' => 'o:remove_columns_admin_media',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Remove Media browse columns', // @translate
                'info' => 'If need remove Media browse columns on page edit user.', // @translate
                'element_group' => 'media'
            ],
            'attributes' => [
                'id' => 'remove_columns_admin_media'
            ]
        ]);

        $optionsFieldset->add([
            'name' => 'o:remove_browse_defaults_admin_media',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Remove Media browse defaults', // @translate
                'info' => 'If need remove Media browse defaults on page edit user.', // @translate
                'element_group' => 'media'
            ],
            'attributes' => [
                'id' => 'remove_browse_defaults_admin_media'
            ]
        ]);

        $optionsFieldset->add([
            'name' => 'o:list_media_types',
            'type' => 'Select',
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select media types', // @translate
                'multiple' => true,
                'id' => 'list_media_types',
            ],
            'options' => [
                'element_group' => 'media',
                'label' => 'List media types', // @translate
                'empty_option' => '', // @translate
                'value_options' => $this->getListMediaTypes(),
                'info' => ''
            ],
        ]);

        $optionsFieldset->add([
            'name' => 'o:list_disallowed_media_types',
            'type' => 'checkbox',
            'options' => [
                'label' => 'List disallowed media types', // @translate
                'info' => 'If checked List media types is list disallowed elements', // @translate
                'element_group' => 'media'
            ],
            'attributes' => [
                'id' => 'list_media_types_is_exeption'
            ]
        ]);
        
        if(!empty($aops = $this->getSets('addition_role_information'))){
            foreach($aops as $aoname => $aolabel)
            $optionsFieldset->add([
                'name' => 'o:'.$aoname,
                'type' => 'text',
                'options' => [
                    'label' => $aolabel,
                    'element_group' => 'information'
                ],
                'attributes' => [
                    'id' => $aoname
                ]
            ]);
        }

        return $optionsFieldset;

    }

    private function getFormPermissions()
    {

        if(empty($this->options['parent'])){

            $resource_classes = $this->getPermissionsClasses();
            $permissions = $this->getPermissionsRules();

            if(!empty($resource_classes) && !empty($permissions)){

                // ksort($resource_classes);
                ksort($permissions);

                foreach($permissions as $class => $cv){
                    foreach($cv as $resource => $rv){
                        foreach($rv as $label => $privileges){
                            // foreach($privileges as $privilege){

                                if($this->resourceExists($resource) && $this->isAllowedSet($class, $resource, $privileges)){
                                    $permission_element_groups[$class] = (!empty($egname = $resource_classes[$class]) ? $egname : $class);
                                    $key = $class.'-'.strtr($label, [' ' => '_']);
                                    $permission_element[$key]['element_group'] = $class;
                                    $permission_element[$key]['label'] = $this->getPermissionLabel($label);
                                    // $permission_element[$key]['pid'][] = $permission->id();
                                }

                            // }
                        }
                    }
                }

                $permissionsFieldset = $this->get('permissions');
                // $permission_element_groups = array_intersect_key($resource_classes, $permission_element_groups);
                $permissionsFieldset->setOption('element_groups', $permission_element_groups);

                foreach($permission_element_groups as $pegk => $pegn){
                    $permissionsFieldset ->add([
                        'name' => $pegk.'-all',
                        'type' => Element\Radio::class,
                        'options' => [
                            'element_group' => $pegk,
                            'label' => 'Set all for it class', // @translate
                            'value_options' => [
                                '' => 'No change', // @translate
                                'allow' => 'Allow', // @translate
                                'deny' => 'Deny', // @translate
                                'set' => 'Specified separately', // @translate
                                // '' //
                            ],
                        ],
                        'attributes' => [
                            'class' => 'setting permission-set-all',
                            'value' => 'set'
                        ],
                    ]);
                    $this->allow_empty[]['permissions'] = $pegk.'-all';
                }

                foreach($permission_element as $element => $value){

                    // $permissionsFieldset->add([
                    //     'name' => $element.'-pid',
                    //     'type' => 'hidden',
                    //     'options' => [
                    //         'label' => $element.'-pid',
                    //     ],
                    //     'attributes' => [
                    //         'value' => join(',', $value['pid']),
                    //     ],
                    // ]);

                    $permissionsFieldset ->add([
                        'name' => $element,
                        'type' => Element\Radio::class,
                        'options' => [
                            'element_group' => $value['element_group'],
                            'label' => $value['label'],
                            'value_options' => [
                                '' => 'No change', // @translate
                                'allow' => 'Allow', // @translate
                                'deny' => 'Deny', // @translate
                            ],
                        ],
                        'attributes' => [
                            'class' => 'setting '.$value['element_group'].'-all permission-set-specified',
                            'value' => '',
                        ],
                    ]);
                    $this->allow_empty[]['permissions'] = $element;

                }
            }
        }

    }

}
