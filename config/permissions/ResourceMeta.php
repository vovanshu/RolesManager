<?php

return [
  'classes' => [
    'resourcemeta' => 'Resource Meta', // @translate
  ],
  'labels' => [
    // 'browse' => 'Browse',  // @translate
  ],
  'rules' => [
    'resourcemeta' => [
      'ResourceMeta\Controller\Admin\Index' => [
        'browse' => [
          'index',
        ],
      ],
    ],
  ],
];
