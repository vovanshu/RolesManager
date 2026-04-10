<?php

return [
  'labels' => [
	  'bulkexport' => 'BulkExport', // @translate
    'bulkexport_own' => 'BulkExport own', // @translate
    'bulkexport_admin' => 'BulkExport admin', // @translate
  ],
  'rules' => [
    'modules' => [
      'BulkExport\Api\Adapter\ShaperAdapter' => [
        'bulkexport' => [
          'search',
        ],
      ],
      'BulkExport\Controller\Output'=> [
        'bulkexport' => [
          'index', 'browse', 'show',
        ],
      ],
      'BulkExport\Controller\Omeka\Controller\Api'=> [
        'bulkexport' => [],
      ],
      'BulkExport\Controller\Omeka\Controller\ApiLocal'=> [
        'bulkexport' => [],
      ],

      'BulkExport\Controller\Admin\BulkExport' => [
        'bulkexport' => [],
      ],
      'BulkExport\Controller\Admin\Exporter' => [
        'bulkexport_admin' => [
          'add', 'start', 'edit', 'configure', 'delete',
        ],
      ],
      'BulkExport\Controller\Admin\Export' => [
        'bulkexport' => [
          'browse', 'index', 'show', 'logs', 'delete-confirm', 'delete',
        ],
      ],
      'BulkExport\Api\Adapter\ExporterAdapter' => [
        'bulkexport_admin' => [
          'search', 'read', 'create', 'update', 'delete',
        ],
      ],
      'BulkExport\Api\Adapter\ExportAdapter' => [
        'bulkexport' => [
          'search', 'read', 'create', 'update', 'delete',
        ],
      ],
      'BulkExport\Entity\Exporter' => [
        'bulkexport' => [
          'read', 
        ],
        'bulkexport_admin' => [
          'create', 'update', 'delete', 
        ],
        'bulkexport_own' => [
          'read' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion', 
          'update' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion', 
          'delete' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion', 
        ],
      ],
      'BulkExport\Entity\Export' => [
        'bulkexport' => [
          'create',
        ],
        'bulkexport_admin' => [
          'read', 'update', 'delete', 
        ],
        'bulkexport_own' => [
          'read' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion', 
          'update' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion', 
          'delete' => 'Omeka\Permissions\Assertion\OwnsEntityAssertion', 
        ],
      ],
    ],
  ],
];
