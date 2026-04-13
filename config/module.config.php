<?php declare(strict_types=1);

/**
 */
namespace RolesManager;

return [
    'permissions' => [
        'classes' => [
            'roles' => 'Roles', // @translate
        ],
        'labels' => [
            'change_native' => 'Change native',  // @translate
            'update_doctrine' => 'Update Doctrine',  // @translate
            'backups' => 'Backups',  // @translate
        ],
        'rules' => [
            'roles' => [
                'RolesManager\Controller\Admin\settingsController' => [
                    'settings' => [
                        'edit',
                    ],
                    'update_doctrine' => [
                        'updoctrine',
                    ],
                    'backups' => [
                        'backups', 'backuping', 'details', 'delete', 'delete-confirm', 'restore', 'restore-confirm',
                    ],
                ],
                'RolesManager\Api\Adapter\RoleAdapter' => [
                    'add' => [
                        'create',
                    ],
                    'delete' => [
                        'delete',
                    ],
                    'edit' => [
                        'update',
                    ],
                    'browse' => [
                        'read', 'search',
                    ],
                ],
                'RolesManager\Controller\Admin\RoleController' => [
                    'browse' => [
                        'browse', 'search', 'show', 'show-details',
                    ],
                    'add' => [
                        'add',
                    ],
                    'change_native' => [
                        'mod',
                    ],
                    'edit' => [
                        'edit',
                    ],
                    'delete' => [
                        'delete', 'delete-confirm',
                    ],
                ],
                'RolesManager\Controller\Admin\ImportController' => [
                    'import' => [
                        'browse', 'search', 'show', 'upload', 'delete', 'delete-confirm', 'import'
                    ],
                ],
                'RolesManager\Entity\Roles' => [
                    'browse' => [
                        'read',
                    ],
                    'add' => [
                        'create',
                    ],
                    'edit' => [
                        'update',
                        [
                            'update' => 'RolesManager\Permissions\Assertion\IsNoYourRoleAssertion',
                        ],
                    ],
                    'delete' => [
                        'delete',
                        [
                            'delete' => 'RolesManager\Permissions\Assertion\IsNoYourRoleAssertion',
                        ],
                    ],
                ],
            ],
        ],
    ],
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
            'RolesManager' => Service\ControllerPlugin\GeneralPluginFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            'Omeka\Acl' => Service\AclFactory::class,
            'RolesManager' => Service\ControllerPlugin\GeneralPluginFactory::class,
        ],
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
    'RolesManager' => [
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
        ],
        'options' => [
            'backup_users' => 'roles_manager_backup_users',
            'show_owned' => 'roles_manager_show_owned',
            'viewer_can_assign_items' => 'roles_manager_viewer_can_assign_items',
            'withoutowner_site_selector' => 'roles_manager_withoutowner_site_selector',
            'withoutowner_item_set_selector' => 'roles_manager_withoutowner_item_set_selector',
            'addition_role_information' => 'roles_manager_addition_role_information',
            'addition_user_information' => 'roles_manager_addition_user_information',
        ],
        'partials_AdvancedSearch' => [
            'common/advanced-search/sort' => 'Sort', // @translate
            'common/advanced-search/fulltext' => 'Fulltext', // @translate
            'common/advanced-search/properties' => 'Properties', // @translate       
            'common/advanced-search/properties-improved' => 'Properties improved', // @translate
            'common/advanced-search/filters' => 'Filters', // @translate
            'common/advanced-search/resource-class' => 'Resource class', // @translate
            'common/advanced-search/resource-template' => 'Resource template', // @translate
            'common/advanced-search/item-sets' => 'Item sets', // @translate
            'common/advanced-search/site' => 'Site', // @translate
            'common/advanced-search/site-improved' => 'Site improved', // @translate
            'common/advanced-search/has-media' => 'Has media', // @translate
            'common/advanced-search/has-original' => 'Has original', // @translate
            'common/advanced-search/has-thumbnails' => 'Has thumbnails', // @translate
            'common/advanced-search/owner' => 'Owner', // @translate
            'common/advanced-search/owner-improved' => 'Owner improved', // @translate
            'common/advanced-search/visibility' => 'Visibility', // @translate
            'common/advanced-search/visibility-radio' => 'Visibility radio', // @translate
            'common/advanced-search/ids' => 'Ids', // @translate
            'common/advanced-search/media-ingester' => 'Media ingester', // @translate
            'common/advanced-search/media-types' => 'Media types', // @translate
            'common/advanced-search/media-types-improved' => 'Media types improved', // @translate
            'common/advanced-search/data-type-geography' => 'data-type-geography', // @translate
            'common/numeric-data-types-advanced-search' => 'numeric-data-types-advanced-search', // @translate
        ],
        'imitation_fields' => ['no-display-values', 'hidden-properties-in-item-form'],
        'AllowSetRulesOnlyGlobalAdmin' => ['roles'],
    ],
];
