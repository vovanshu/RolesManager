<?php

return [
  'classes' => [
    'datavis' => 'Datavis',
  ],
  'rules' => [
    'datavis' => [
      'Datavis\Api\Adapter\DatavisVisAdapter' => [
        'show'=> [
          'search', 'read',
        ],
        'add'=> [
          'create',
        ],
        'edit'=> [
          'update',
        ],
        'delete'=> [
          'delete',
        ],
      ],
      'Datavis\Controller\SiteAdmin\Index' => [
        'browse' => [
          'index', 'browse',
        ],
        'add'=> [
          'add',
        ],
        'edit'=> [
          'edit',
        ],
        'delete'=> [
          'delete',
        ],
        'add-dataset-type'=> [
          'add-dataset-type',
        ],
        'get-diagram-fieldset'=> [
          'get-diagram-fieldset',
        ],
        'dataset'=> [
          'dataset',
        ],
        'diagram'=> [
          'diagram',
        ],
      ],
      'Datavis\Controller\Site\Index' => [
        'edit'=> [
          'dataset', 'diagram',
        ],
      ],
      'Datavis\Entity\DatavisVis' => [
        'edit'=> [
          [
            'update' => 'Laminas\Permissions\Acl\Assertion\AssertionAggregate',
          ],
        ],
        'delete'=> [
          [
            'delete' => 'Laminas\Permissions\Acl\Assertion\AssertionAggregate',
          ],
        ],
        'show'=> [
          'read',
        ],
        'add'=> [
          'create',
        ],
      ],
    ],
  ],
];
