<?php

return [
  'classes' => [
    'advancedsearch' => 'Advanced Search',
  ],
  'rules' => [
    'advancedsearch' => [
      'AdvancedSearch\Controller\Admin\IndexController' => [
        'browse' => [
          'browse',
        ],
      ],
      'AdvancedSearch\Controller\Admin\SearchConfigController' => [
        'add' => [
          'add',
        ],
        'edit' => [
          'edit', 'copy'
        ],
        'delete' => [
          'delete',
        ],
      ],
      'AdvancedSearch\Controller\Admin\SearchEngineController' => [
        'browse' => [
          'index',
        ],
        'add' => [
          'add',
        ],
        'edit' => [
          'edit'
        ],
        'delete' => [
          'delete',
        ],
      ],
      'AdvancedSearch\Controller\Admin\SearchSuggesterController' => [
        'browse' => [
          'index',
        ],
        'add' => [
          'add',
        ],
        'edit' => [
          'edit'
        ],
        'delete' => [
          'delete',
        ],
      ],
    ],
  ],
];
