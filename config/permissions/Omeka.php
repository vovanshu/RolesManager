<?php

return [
  'classes' => [
    'admin' => 'Admin',  // @translate
    'assets' => 'Assets',  // @translate
    'annotation' => 'Annotation',  // @translate
    'items' => 'Items',  // @translate
    'itemSets' => 'Item Sets',  // @translate
    'navigation' => 'Navigation',  // @translate
    'media' => 'Media',  // @translate
    'modules' => 'Modules',  // @translate
    'pages' => 'Pages',  // @translate
    'jobs' => 'Jobs',  // @translate
    'resources' => 'Resources',  // @translate
    'resourceTemplates' => 'Resource templates',  // @translate
    'settings' => 'Settings',  // @translate
    'sites' => 'Sites',  // @translate
    'siteInfo' => 'Site admin',  // @translate
    'theme' => 'Theme',  // @translate
    'users' => 'Users',  // @translate
    'vocabulary' => 'Vocabularies',  // @translate
  ],
  'labels' => [
    'admin_dashboard' => 'Admin dashboard',  // @translate
    'browse' => 'Browse',  // @translate
    'show' => 'Show',  // @translate
    'show_all' => 'Show all',  // @translate
    'add' => 'Add',  // @translate
    'delete' => 'Delete',  // @translate
    'edit' => 'Edit',  // @translate
    'edit_owned' => 'Edit your owned', // @translate
    'delete_owned' => 'Delete your owned', // @translate
    'edit_selected' => 'Edit selected', // @translate
    'edit_all' => 'Edit all', // @translate
    'delete_selected' => 'Delete selected', // @translate
    'delete_all' => 'Delete all', // @translate
    'assign_items' => 'Can assign items', // @translate   
    'add_page' => 'Add page', // @translate
    'add_visualization' => 'Add visualization', // @translate
    'system_info' => 'System info', // @translate
    'settings' => 'Settings',  // @translate
    'settings_theme' => 'Settings theme', // @translate
    'activate' => 'Activate',  // @translate
    'deactivate' => 'Deactivate',  // @translate
    'install' => 'Install',  // @translate
    'uninstall' => 'Uninstall',  // @translate
    'upgrade' => 'Upgrade',  // @translate
    'add_property' => 'Add new property row',  // @translate
    'import' => 'Import',  // @translate
    'show_himself' => 'Show himself', // @translate
    'edit_himself' => 'Edit himself', // @translate
    'edit_others' => 'Edit others', // @translate
    'change_password_himself' => 'Change password himself', // @translate
    'change_password_others' => 'Change password others', // @translate
    'change_api-keys_himself' => 'Change Api-keys himself', // @translate
    'change_api-keys_others' => 'Change Api-keys others', // @translate
    'delete_himself' => 'Delete himself', // @translate
    'delete_others' => 'Delete others', // @translate
    'change_role' => 'Change role', // @translate
    'change_role_himself' => 'Change role himself', // @translate
    'change_role_for_admin' => 'Change role for admin', // @translate
    'activate_user' => 'Activate user', // @translate
    'activate_user_himself' => 'Activate user himself', // @translate
  ],
  'rules' => [
    'admin'=> [
      'Omeka\Controller\Admin\Index'=> [
        'admin_dashboard' => [
          'index', 'browse', 'show', 'show-details',
        ],
      ],
      'Omeka\Entity\Item'=> [
        'admin_dashboard' => [
          'read',
        ],
      ],
      'Omeka\Api\Adapter\ItemAdapter'=> [
        'admin_dashboard' => [
          'search', 'read',
        ],
      ],
      'Omeka\Controller\Admin\Item'=> [
        'admin_dashboard' => [
          'index', 'browse', 'sidebar-select', 'show', 'show-details', 'search',
        ],
      ],
      'Omeka\Controller\Admin\Setting'=> [
        'settings' => [
          'browse',
        ],
      ],
      'Omeka\Controller\Admin\SystemInfo'=> [
        'system_info' => [
          'index', 'browse', 'show', 'show-details',
        ],
      ],
    ],
    'resources'=> [
      'Omeka\Api\Adapter\ApiResourceAdapter'=> [
        'show' => [
          'search', 'read',
        ],
      ],
      'Omeka\Api\Adapter\DataTypeAdapter'=> [
        'show' => [
          'search', 'read',
        ],
      ],
      'Omeka\Api\Adapter\PropertyAdapter'=> [
        'show' => [
          'search', 'read',
        ],
      ],
      'Omeka\Api\Adapter\ResourceAdapter'=> [
        'show' => [
          'read',
        ],
      ],
      'Omeka\Api\Adapter\ResourceClassAdapter'=> [
        'show' => [
          'search', 'read',
        ],
      ],
      'Omeka\Controller\Admin\Property'=> [
        'browse' => [
          'index', 'browse',
        ],
        'show' => [
          'show', 'show-details',
        ],
      ],
      'Omeka\Controller\Admin\Query'=> [
        'browse' => [
          'sidebar-edit', 'sidebar-preview', 'search-filters',
        ],
      ],
      'Omeka\Controller\Admin\ResourceClass'=> [
        'browse' => [
          'index', 'browse',
        ],
        'show' => [
          'show', 'show-details',
        ],
      ],
      'Omeka\Entity\Property'=> [
        'show' => [
          'read',
        ],
      ],
      'Omeka\Entity\Resource'=> [
        'show_all' => [
          'view-all',
        ],
      ],
      'Omeka\Entity\ResourceClass'=> [
        'show' => [
          'read',
        ],
      ],
    ],
    'assets'=> [
      'Omeka\Api\Adapter\AssetAdapter'=> [
        'show' => [
          'search', 'read',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'delete' => [
          'delete',
        ],
      ],
      'Omeka\Controller\Admin\Asset'=> [
        'browse' => [
          'browse', 'sidebar-select',
        ],
        'show' => [
          'show', 'show-details',
        ],
        'add' => [
          'add',
        ],
        'edit' => [
          'edit',
        ],
        'delete' => [
          'delete', 'delete-confirm',
        ],
      ],
      'Omeka\Entity\Asset'=> [
        'show' => [
          'read',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'edit_owned' => [
          [
            'update' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion',
          ],
        ],
        'delete' => [
          'delete',
        ],
        'delete_owned' => [
          [
            'delete' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion',
          ],
        ],
      ],
    ],
    'items'=> [
      'Omeka\Api\Adapter\ItemAdapter'=> [
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'edit_selected' => [
          'batch_update',
        ],
        'edit_all' => [
          'batch_update_all',
        ],
        'delete' => [
          'delete',
        ],
        'delete_selected' => [
          'batch_delete',
        ],
        'delete_all' => [
          'batch_delete_all',
        ],
      ],
      'Omeka\Controller\Admin\Item'=> [
        'add' => [
          'add',
        ],
        'edit' => [
          'edit',
        ],
        'edit_selected' => [
          'batch-edit',
        ],
        'edit_all' => [
          'batch-edit-all',
        ],
        'delete' => [
          'delete', 'delete-confirm',
        ],
        'delete_selected' => [
          'batch-delete',
        ],
        'delete_all' => [
          'batch-delete-all',
        ],
      ],
      'Omeka\Entity\Item'=> [
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'edit_owned' => [
          [
            'update' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion',
          ],
        ],
        'delete' => [
          'delete',
        ],
        'delete_owned' => [
          [
            'delete' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion',
          ],
        ],
      ],
      'Omeka\Entity\ValueAnnotation'=> [
        'browse' => [
          'read',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'delete' => [
          'delete',
        ],
      ],
    ],
    'itemSets'=> [
      'Omeka\Api\Adapter\ItemSetAdapter'=> [
        'show' => [
          'search', 'read',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'edit_selected' => [
          'batch_update',
        ],
        'edit_all' => [
          'batch_update_all',
        ],
        'delete' => [
          'delete',
        ],
        'delete_selected' => [
          'batch_delete',
        ],
        'delete_all' => [
          'batch_delete_all',
        ],
      ],
      'Omeka\Controller\Admin\ItemSet'=> [
        'browse' => [
          'index', 'browse', 'sidebar-select',
        ],
        'show' => [
          'show', 'show-details', 'search',
        ],
        'add' => [
          'add',
        ],
        'edit' => [
          'edit',
        ],
        'edit_selected' => [
          'batch-edit',
        ],
        'edit_all' => [
          'batch-edit-all',
        ],
        'delete' => [
          'delete', 'delete-confirm',
        ],
        'delete_selected' => [
          'batch-delete',
        ],
        'delete_all' => [
          'batch-delete-all',
        ],
      ],
      'Omeka\Entity\ItemSet'=> [
        'show' => [
          'read',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'edit_owned' => [
          [
            'update' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion',
          ],
        ],
        'delete' => [
          'delete',
        ],
        'delete_owned' => [
          [
            'delete' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion',
          ],
        ],
      ],
    ],
    'media'=> [
      'Omeka\Api\Adapter\MediaAdapter'=> [
        'show' => [
          'search', 'read',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'edit_selected' => [
          'batch_update',
        ],
        'edit_all' => [
          'batch_update_all',
        ],
        'delete' => [
          'delete',
        ],
        'delete_selected' => [
          'batch_delete',
        ],
        'delete_all' => [
          'batch_delete_all',
        ],
      ],
      'Omeka\Controller\Admin\Media'=> [
        'browse' => [
          'index', 'browse', 'sidebar-select',
        ],
        'show' => [
          'show', 'show-details', 'search',
        ],
        'add' => [
          'add',
        ],
        'edit' => [
          'edit',
        ],
        'edit_selected' => [
          'batch-edit',
        ],
        'edit_all' => [
          'batch-edit-all',
        ],
        'delete' => [
          'delete', 'delete-confirm',
        ],
        'delete_selected' => [
          'batch-delete',
        ],
        'delete_all' => [
          'batch-delete-all',
        ],
      ],
      'Omeka\Entity\Media'=> [
        'show' => [
          'read',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'edit_owned' => [
          [
            'update' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion',
          ],
        ],
        'delete' => [
          'delete',
        ],
        'delete_owned' => [
          [
            'delete' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion',
          ],
        ],
      ],
    ],
    'resourceTemplates'=> [
      'Omeka\Api\Adapter\ResourceTemplateAdapter'=> [
        'show' => [
          'search', 'read',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'delete' => [
          'delete',
        ],
      ],
      'Omeka\Controller\Admin\ResourceTemplate'=> [
        'browse' => [
          'index', 'browse',
        ],
        'show' => [
          'show', 'show-details',
        ],
        'add' => [
          'add', 'add-new-property-row',
        ],
        'import' => [
          'import',
        ],
        'edit' => [
          'edit',
        ],
        'delete' => [
          'delete', 'delete-confirm',
        ],
      ],
      'Omeka\Entity\ResourceTemplate'=> [
        'show' => [
          'read',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'delete' => [
          'delete',
        ],
      ],
    ],
    'users'=> [
      'Omeka\Api\Adapter\UserAdapter'=> [
        'show' => [
          'search', 'read',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'delete' => [
          'delete',
        ],
      ],
      'Omeka\Controller\Admin\User'=> [
        'browse' => [
          'browse',
        ],
        'show' => [
          'show',
        ],
        'add' => [
          'add',
        ],
        'edit' => [
          'edit',
        ],
        'delete' => [
          'delete',
        ],
      ],
      'Omeka\Entity\User'=> [
        'show' => [
          'read',
        ],
        'show_himself' => [
          [
            'read' => 'Omeka\Permissions\Assertion\IsSelfAssertion',
          ],
        ],
        'add' => [
          'create',
        ],
        'edit_himself' => [
          [
            'update' => 'Omeka\Permissions\Assertion\IsSelfAssertion',
          ],
        ],
        'edit_others' => [
          [
            'update' => 'RolesManager\Permissions\Assertion\UsersUpdateAssertion',
          ],
        ],
        'change_password_himself' => [
          [
            'change-password' => 'Omeka\Permissions\Assertion\IsSelfAssertion',
          ],
        ],
        'change_password_others' => [
          [
            'change-password' => 'RolesManager\Permissions\Assertion\UsersUpdateAssertion',
          ],
        ],
        'change_api-keys_himself' => [
          [
            'edit-keys' => 'Omeka\Permissions\Assertion\IsSelfAssertion',
          ],
        ],
        'change_api-keys_others' => [
          [
            'edit-keys' => 'RolesManager\Permissions\Assertion\UsersUpdateAssertion',
          ],
        ],
        'delete_himself' => [
          [
            'delete' => 'Omeka\Permissions\Assertion\IsSelfAssertion',
          ],
        ],
        'delete_others' => [
          [
            'delete' => 'RolesManager\Permissions\Assertion\UsersUpdateAssertion',
          ],
        ],
        'change_role' => [
          'change-role',
        ],
        'change_role_himself' => [
          [
            'change-role' => 'Omeka\Permissions\Assertion\IsSelfAssertion',
          ],
        ],
        'change_role_for_admin' => [
          'change-role-admin',
        ],
        'activate_user' => [
          'activate-user',
        ],
        'activate_user_himself' => [
          [
            'activate-user' => 'Omeka\Permissions\Assertion\IsSelfAssertion',
          ],
        ],
      ],
      'Omeka\Controller\Admin\Columns'=> [
        'edit' => [
          'column-list', 'column-row', 'column-edit-sidebar',
        ],
      ],
    ],
    'vocabulary'=> [
      'Omeka\Api\Adapter\VocabularyAdapter'=> [
        'show' => [
          'search', 'read',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'delete' => [
          'delete',
        ],
      ],
      'Omeka\Controller\Admin\Vocabulary'=> [
        'browse' => [
          'index', 'browse', 'classes', 'properties',
        ],
        'show' => [
          'show', 'show-details',
        ],
      ],
      'Omeka\Entity\Vocabulary'=> [
        'show' => [
          'read',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          [
            'update' => 'RolesManager\Permissions\Assertion\UsersUpdateAssertion',
          ],
        ],
        'delete' => [
          [
            'delete' => 'RolesManager\Permissions\Assertion\UsersUpdateAssertion',
          ],
        ],
      ],
    ],
    'modules'=> [
      'Omeka\Controller\Admin\Module'=> [
        'browse' => [
          'browse',
        ],
        'delete' => [
          'delete',
        ],
        'activate' => [
          'activate',
        ],
        'deactivate' => [
          'deactivate',
        ],
        'install' => [
          'install',
        ],
        'uninstall' => [
          'uninstall',
        ],
        'upgrade' => [
          'upgrade',
        ],
        'settings' => [
          'configure',
        ],
      ],
      'Omeka\Module\Manager'=> [
        'activate' => [
          'activate',
        ],
        'deactivate' => [
          'deactivate',
        ],
        'install' => [
          'install',
        ],
        'uninstall' => [
          'uninstall',
        ],
        'upgrade' => [
          'upgrade',
        ],
        'settings' => [
          'configure',
        ],
      ],
    ],
    'sites' => [
      'Omeka\Api\Adapter\SiteAdapter'=> [
        'show' => [
          'search', 'read',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          'update',
        ],
        'delete' => [
          'delete',
        ],
      ],
      'Omeka\Controller\SiteAdmin\Index' => [
        'browse' => [
          'index',
        ],
        'show' => [
          'show',
        ],
        'add' => [
          'add',
        ],
        'delete' => [
          'delete', 'delete-confirm',
        ],
        'settings' => [
          'edit', 'users', 'navigation', 'resources', 'sidebar-item-select', 'theme', 'theme-settings', 'theme-resource-pages'
        ],
      ],
      'Omeka\Entity\Site'=> [
        'show' => [
          [
            'read' => 'RolesManager\Permissions\Assertion\SitesUpdateAssertion',
          ],
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          [
            'update' => 'RolesManager\Permissions\Assertion\SitesUpdateAssertion',
          ],
        ],
        'delete' => [
          'delete',
        ],
        'delete_owned' => [
          [
            'delete' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion',
          ],
        ],
        'assign_items' => [
          [
            'can-assign-items' => 'RolesManager\Permissions\Assertion\SitesUpdateAssertion',
          ],
        ],
        // 'add_visualization' => [
        //   [
        //     'add-visualization' => 'Omeka\Permissions\Assertion\HasSitePermissionAssertion',
        //   ],
        // ],
      ],
    ],
    'pages'=> [
      'Omeka\Entity\SitePage'=> [
        'show' => [
          [
            'read' => 'RolesManager\Permissions\Assertion\PagesUpdateAssertion',
          ],
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          [
            'update' => 'RolesManager\Permissions\Assertion\PagesUpdateAssertion',
          ],
        ],
        'delete' => [
          [
            'delete' => 'RolesManager\Permissions\Assertion\PagesUpdateAssertion',
          ],
        ],
      ],
      'Omeka\Controller\SiteAdmin\Index' => [
        'add' => [
          'add-page',
        ],
      ],
      'Omeka\Entity\Site'=> [
        'add' => [
          [
            'add-page' => 'RolesManager\Permissions\Assertion\SitesUpdateAssertion',
          ],
        ],
      ],
    ],
  ],
];
