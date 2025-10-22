<?php

return [
  'classes' => [
    'reference' => 'Reference',
  ],
  'rules' => [
    'reference' => [
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
