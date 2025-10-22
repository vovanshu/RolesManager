<?php

return [
  'classes' => [
    'advancedresourcetemplate' => 'Advanced Resource Template',  // @translate
  ],
  'rules' => [
    'advancedresourcetemplate' => [
      'AdvancedResourceTemplate\Api\Adapter\ResourceTemplateAdapter' => [
        'show' => [
          'read', 'search',
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
      'AdvancedResourceTemplate\Entity\ResourceTemplateData' => [
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
      'AdvancedResourceTemplate\Entity\ResourceTemplatePropertyData' => [
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
      'AdvancedResourceTemplate\Controller\Admin\Index' => [
        'browse' => [
          'values',
        ],
      ],
    ],
  ],
];
