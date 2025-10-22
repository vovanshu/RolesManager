<?php

return [
  // 'classes' => [
  //   'easyadmin' => 'EasyAdmin',  // @translate
  // ],
  'labels' => [
	  'easyadmin' => 'EasyAdmin',  // @translate
  ],
  'rules' => [
    'modules' => [
      'EasyAdmin\Controller\Admin\Addons' => [
        'easyadmin' => [
          'browse', 'index',
        ],
      ],
      'EasyAdmin\Controller\Admin\CheckAndFix'=> [
        'easyadmin' => [
          'browse', 'index',
        ],
      ],
      'EasyAdmin\Controller\Admin\Cron'=> [
        'easyadmin' => [
          'browse', 'index',
        ],
      ],
    ],
  ],
];
