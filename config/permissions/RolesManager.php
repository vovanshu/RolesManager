<?php

return [
  'classes' => [
    'roles' => 'Roles', // @translate
    // 'permissions' => 'Permissions', // @translate
  ],
  'labels' => [
    // 'browse' => 'Browse',  // @translate
    // 'show' => 'Show',  // @translate
    // 'add' => 'Add',  // @translate
    // 'delete' => 'Delete',  // @translate
    // 'edit' => 'Edit',  // @translate
    'change_native' => 'Change native',  // @translate
    // 'settings' => 'Settings',  // @translate
    'update_doctrine' => 'Update Doctrine',  // @translate
    'backups' => 'Backups',  // @translate
  ],
  'rules' => [
    // 'permissions' => [
    //   'RolesManager\Api\Adapter\PermissionAdapter' => [
    //     'browse' => [
    //       'read',
    //     ],
    //     'show' => [
    //       'search',
    //     ],
    //     'add' => [
    //       'create',
    //     ],
    //     'delete' => [
    //       'delete',
    //     ],
    //     'edit' => [
    //       'edit',
    //     ],
    //   ],
    //   'RolesManager\Controller\Admin\PermissionController' => [
    //     'add' => [
    //       'add', 'preset-privileges',
    //     ],
    //     'browse' => [
    //       'browse', 'show-details', 'show',
    //     ],
    //     'delete' => [
    //       'delete', 'delete-confirm',
    //     ],
    //     'edit' => [
    //       'edit',
    //     ],
    //   ],
    //   'RolesManager\Entity\Permissions' => [
    //     'browse' => [
    //       'read',
    //     ],
    //     'add' => [
    //       'create',
    //     ],
    //     'delete' => [
    //       'delete',
    //     ],
    //     'edit' => [
    //       'edit',
    //     ],
    //   ],
    // ],
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
          'read',
          'search',
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
];
