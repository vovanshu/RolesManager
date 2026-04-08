<?php declare(strict_types=1);

/**
 */
namespace RolesManager;

return [
    'permissions' => $permissions,
    'api_adapters' => [
        'invokables' => [
            'roles' => Api\Adapter\RoleAdapter::class,
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            dirname(__DIR__) . '/src/Entity',
        ],
        'proxy_paths' => [
            dirname(__DIR__) . '/data/doctrine-proxies',
        ],
        'filters' => [
            // 'resource_visibility' => Db\Filter\ResourceVisibilityFilter::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'itemSetSelector' => View\Helper\ItemSetSelector::class,
            'siteSelector' => View\Helper\SiteSelector::class,
        ],
        'factories' => [
            'RolesManagerCommon' => Service\ControllerPlugin\CommonPluginFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            'Omeka\Acl' => Service\AclFactory::class,
            'RolesManager\Common' => Service\ControllerPlugin\CommonPluginFactory::class,
        ],
        // 'delegators' => [
        //     'Omeka\Acl' => [
        //         Service\AclFactory::class
        //     ],
        // ]
    ],
    'controllers' => [
        'factories' => [
            Controller\Admin\RoleController::class => Service\Controller\Admin\RoleControllerFactory::class,
            Controller\Admin\SettingsController::class => Service\Controller\Admin\SettingsControllerFactory::class,
            Controller\Admin\ImportController::class => Service\Controller\Admin\ImportControllerFactory::class,
            'Omeka\Controller\Admin\Index' => Service\Controller\Admin\IndexControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            'Omeka\Form\UserForm' => Service\Form\UserFormFactory::class,
            Form\Element\ParentRoleSelect::class => Service\Form\Element\ParentRoleSelectFactory::class,
            Form\Element\RoleSelect::class => Service\Form\Element\RoleSelectFactory::class,
            'RolesManager\Form\Element\RoleNativeSelect' => Service\Form\Element\RoleNativeSelectFactory::class,
            Form\RoleAddForm::class => Service\Form\RoleAddFormFactory::class,
            Form\RoleModForm::class => Service\Form\RoleModFormFactory::class,
            Form\RoleEditForm::class => Service\Form\RoleEditFormFactory::class,
        ],
    ],
    'column_types' => [
        'invokables' => [
            'owner' => ColumnType\Owner::class,
        ],
        // 'factories' => [
            // 'theme' => Service\ColumnType\ThemeFactory::class,
            // 'value' => Service\ColumnType\ValueFactory::class,
        // ],
    ],
    'navigation' => [
        'AdminGlobal' => [
            [
                'label' => 'Roles Manager', // @translate
                'class' => 'o-icon-users',
                'route' => 'admin/roles',
                'resource' => Controller\Admin\RoleController::class,
                'controller' => 'roles',
                'privilege' => 'browse',
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'roles-manager-settings' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/roles-manager-settings[/:action][/:name]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'name' => '[.a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'RolesManager\Controller\Admin',
                                '__CONTROLLER__' => 'settings',
                                'controller' => Controller\Admin\SettingsController::class,
                                'action' => 'edit',
                            ],
                        ],
                    ],
                    'roles-manager-import' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/roles-manager-import[/:action][/:name]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'name' => '[.a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'RolesManager\Controller\Admin',
                                '__CONTROLLER__' => 'roles-import',
                                'controller' => Controller\Admin\ImportController::class,
                                'action' => 'browse',
                            ],
                        ],
                    ],
                    'roles' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/roles[/:action][/:id]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '\d+',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'RolesManager\Controller\Admin',
                                '__CONTROLLER__' => 'roles',
                                'controller' => Controller\Admin\RoleController::class,
                                'action' => 'browse',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
            [
                'type' => 'gettext',
                'base_dir' => OMEKA_PATH . '/files/languages/RolesManager',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'js_translate_strings' => [
        'Request too long to process.', // @translate
    ],
    // Don't edit these options here: copy this key in your own omeka config/local.config.php
    // and modify options as you want.
    'rolesmanager' => [
        'developing' => False,
        'debug' => False,
        'backups' => OMEKA_PATH.'/files/backup/RolesManager/',
        'imports' => OMEKA_PATH.'/files/import/RolesManager/',
        'settings' => [
            'roles_manager_backup_users' => 'false',
            'roles_manager_show_owned' => 'false',
            'roles_manager_viewer_can_assign_items' => 'false',
            'roles_manager_withoutowner_site_selector' => 'false',
            'roles_manager_withoutowner_item_set_selector' => 'false',
            'roles_manager_addition_role_information' => '',
            'roles_manager_addition_user_information' => '',
            // 'recaptcha_enable_on_login' => 'false',
            // 'recaptcha_enable_on_forgot_password' => 'false',
            // 'recaptcha_ip_white_list' => '',
        ],
        'options' => [
            'backup_users' => 'roles_manager_backup_users',
            'show_owned' => 'roles_manager_show_owned',
            'viewer_can_assign_items' => 'roles_manager_viewer_can_assign_items',
            'withoutowner_site_selector' => 'roles_manager_withoutowner_site_selector',
            'withoutowner_item_set_selector' => 'roles_manager_withoutowner_item_set_selector',
            'addition_role_information' => 'roles_manager_addition_role_information',
            'addition_user_information' => 'roles_manager_addition_user_information',
            // 'recaptcha_enable_on_login' => 'recaptcha_enable_on_login',
            // 'recaptcha_enable_on_forgot_password' => 'recaptcha_enable_on_forgot_password',
            // 'recaptcha_ip_white_list' => 'recaptcha_ip_white_list',
        ],
        // 'labels' => [
        //     'registred_classes' => 'Registred Resource Classes', // @translate
        //     'registred_permissions' => 'Registred Permissions', // @translate
        //     'found_classes' => 'Found Resource Classes', // @translate
        //     'found_permissions' => 'Found Permissions', // @translate
        // ],
        // Apply the groups of item sets to items and medias.
        // 'roles_recursive_item_sets' => true,
        // Apply the item groups to medias. Implied and not taken in account
        // when `group_recursive_item_sets` is true.
        // 'roles_recursive_items' => true,
        'imitation_fields' => ['no-display-values', 'hidden-properties-in-item-form'],
        'AllowSetRulesOnlyGlobalAdmin' => ['roles'],
    ],
];
