<?php

return [
  'classes' => [
    'facetedbrowse' => 'Faceted Browse',
  ],
  'rules' => [
    'facetedbrowse' => [
      'FacetedBrowse\Api\Adapter\FacetedBrowseCategoryAdapter' => [
        'show' => [
          'read',
        ],
      ],
      'FacetedBrowse\Api\Adapter\FacetedBrowsePageAdapter' => [
        'show' => [
          'read',
        ],
      ],
      'FacetedBrowse\Entity\FacetedBrowseCategory' => [
        'show' => [
          'read',
        ],
      ],
      'FacetedBrowse\Entity\FacetedBrowsePage' => [
        'show' => [
          'read',
        ],
      ],
    ],
  ],
];
