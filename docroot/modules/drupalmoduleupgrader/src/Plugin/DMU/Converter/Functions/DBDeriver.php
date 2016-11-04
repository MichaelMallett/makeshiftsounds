<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\DeriverBase;

/**
 * Builds derivative definitions for the _db plugin.
 */
class DBDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_definition) {
    $derivatives = [];

    $functions = [
      'db_select', 'db_insert', 'db_update', 'db_merge', 'db_delete', 'db_truncate',
    ];

    foreach ($functions as $function) {
      $variables = [
        '@function' => $function,
      ];
      $derivative = $base_definition;
      $derivative['function'] = $function;
      $derivative['description'] = $this->t('Disables calls to @function() which refer to legacy tables.', $variables);

      $derivatives[$function] = $derivative;
    }

    return $derivatives;
  }

}
