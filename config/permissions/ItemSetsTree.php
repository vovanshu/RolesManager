<?php

return [
  'classes' => [
    'itemsetstree' => 'Item Sets Tree',
  ],
  'rules' => [
    'itemsetstree' => [
      'ItemSetsTree\Api\Adapter\ItemSetsTreeEdgeAdapter' => [
        'show' => [
          'search',
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
      'ItemSetsTree\Controller\Admin\Index' => [
        'browse' => [
          'index',
        ],
        'add' => [
          'create',
        ],
        'edit' => [
          'edit',
        ],
        'delete' => [
          'delete',
        ],
      ],
      'ItemSetsTree\Entity\ItemSetsTreeEdge' => [
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
  ],
];
