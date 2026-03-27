<?php

return [
  'classes' => [
    'reference' => 'Reference',
  ],
  'rules' => [
    'reference' => [
      'Reference\Controller\Admin\ReferenceController' => [
        'browse' => [
          'browse',
        ],
        'show' => [
          'show', 'values'
        ],
      ],
      'Reference\Controller\Site\ReferenceController' => [
        'browse' => [
          'browse',
        ],
        'show' => [
          'list',
        ],
      ],
    ],
  ],
];
