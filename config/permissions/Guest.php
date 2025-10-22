<?php

return [
  'classes' => [
    'guest' => 'Guest',
  ],
  'labels' => [
    'login' => 'Login',  // @translate
    'session-token' => 'Session token',  // @translate
    'logout' => 'Logout',  // @translate
    'register' => 'Register',  // @translate
    'me' => 'Me',  // @translate
  ],
  'rules' => [
    'guest' => [
      'Guest\Controller\ApiController' => [
        'login' => [
          'login',
        ],
        'session-token' => [
          'session-token',
        ],
        'logout' => [
          'logout',
        ],
        'register' => [
          'register',
        ],
      ],
      'Guest\Controller\Site\GuestController' => [
        'me' => [
          'me',
        ],
      ],
    ],
  ],
];
