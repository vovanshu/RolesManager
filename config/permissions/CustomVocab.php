<?php

return [
  'classes' =>[
    'customvocab' => 'Custom Vocab',
  ],
  'labels' => [
    'browse' => 'Browse',  // @translate
    'show' => 'Show',  // @translate
    'add' => 'Add',  // @translate
    'delete' => 'Delete',  // @translate
    'edit' => 'Edit',  // @translate
    'edit_owned' => 'Edit your owned', // @translate
    'delete_owned' => 'Delete your owned', // @translate
    'edit_selected' => 'Edit selected', // @translate
    'edit_all' => 'Edit all', // @translate
    'delete_selected' => 'Delete selected', // @translate
    'delete_all' => 'Delete all', // @translate
  ],
  'rules' => [
    'customvocab' => [
      'CustomVocab\Api\Adapter\CustomVocabAdapter' => [
        'browse' => [
          'browse',
        ],
        'show' => [
          'search','read',
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
      'CustomVocab\Controller\Admin\Index' => [
        'browse' => [
          'browse', 'show-details',
        ],
        'add' => [
          'add',
        ],
        'edit' => [
          'edit',
        ],
        'delete' => [
          'delete',
        ],
      ],
      'CustomVocab\Entity\CustomVocab' => [
        'show' => [
          'read',
        ],
        'add' => [
          'create',
        ],
        'edit_owned' => [
          [
            'update' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion',
          ],
        ],
        'delete_owned' => [
          [
            'delete' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion',
          ],
        ],
      ],
    ],
  ],
];
