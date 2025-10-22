<?php

return [
  // 'classes' => [
  //   'bulkimport' => 'BulkImport', // @translate
  // ],
  'labels' => [
	  'bulkimport' => 'BulkImport', // @translate
  ],
  'rules' => [
    'modules' => [
      'BulkImport\Controller\Admin\BulkImport' => [
        'bulkimport' => [
          'browse', 'index',
        ],
      ],
      'BulkImport\Controller\Admin\Import'=> [
        'bulkimport' => [
          'browse', 'index', 'logs', 'show', 'stop', 'undo',
        ],
      ],
      'BulkImport\Controller\Admin\Importer'=> [
        'bulkimport' => [
          'browse', 'index', 'add', 'start', 'delete', 'edit', 'configure-processor', 'configure-reader',
        ],
      ],
      'BulkImport\Controller\Admin\Mapping'=> [
        'bulkimport' => [
          'browse', 'index', 'add', 'copy', 'delete', 'edit', 'show',
        ],
      ],

      'BulkImport\Api\Adapter\ImportedAdapter' => [
        'bulkimport' => [
          'search', 'read', 'create', 'update',  'delete',
        ],
      ],
      'BulkImport\Api\Adapter\ImporterAdapter' => [
        'bulkimport' => [
          'search', 'read', 'create', 'update',  'delete',
        ],
      ],
      'BulkImport\Api\Adapter\ImportAdapter' => [
        'bulkimport' => [
          'search', 'read', 'create', 'update',  'delete',
        ],
      ],
      'BulkImport\Api\Adapter\MappingAdapter' => [
        'bulkimport' => [
          'search', 'read', 'create', 'update',  'delete',
        ],
      ],

      'BulkImport\Entry\BaseEntry' => [
        'bulkimport' => [
          'read', 'create', 'update', 'delete',
        ],
      ],
      'BulkImport\Entry\Entry' => [
        'bulkimport' => [
          'read', 'create', 'update', 'delete',
        ],
      ],
      'BulkImport\Entry\JsonEntry' => [
        'bulkimport' => [
          'read', 'create', 'update', 'delete',
        ],
      ],
      'BulkImport\Entry\SimpleXMLElementNamespaced' => [
        'bulkimport' => [
          'read', 'create', 'update', 'delete',
        ],
      ],
      'BulkImport\Entry\SpreadsheetEntry' => [
        'bulkimport' => [
          'read', 'create', 'update', 'delete',
        ],
      ],
      'BulkImport\Entry\XmlEntry' => [
        'bulkimport' => [
          'read', 'create', 'update', 'delete',
        ],
      ],

    ],
  ],
];
