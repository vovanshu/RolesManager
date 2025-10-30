<?php

return [
  'labels' => [
	'adminer_database' => 'Adminer Database',  // @translate
  ],
  'rules' => [
    'modules' => [
      'Adminer\Controller\Admin\IndexController' => [
        'adminer_database' => [
          'index', 'adminerMysql', 'adminerEditorMysql',
        ],
      ],
    ],
  ],
];
