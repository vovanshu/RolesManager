<?php

return [
  'classes' => [
    'log' => 'Logs',
  ],
  'labels' => [
	'show_owned' => 'Show your owned',  // @translate
  ],
  'rules' => [
    'log' => [
      'Log\Api\Adapter\LogAdapter' => [
		'show' => [
          'read', 'search',
        ],
        'add' => [
          'create',
        ],
      ],
      'Log\Controller\Admin\LogController' => [
        'browse' => [
          'browse',
        ],
		'show' => [
          'search', 'show-details',
        ],
      ],
      'Log\Entity\Log' => [
        'show_owned' => [
          [
            'read' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion',
          ],
        ],
        'add' => [
          'create',
        ],
        'show' => [
          'read',
        ],
        'show_all' => [
          'view-all',
        ],
      ],
    ],
  ],
];
