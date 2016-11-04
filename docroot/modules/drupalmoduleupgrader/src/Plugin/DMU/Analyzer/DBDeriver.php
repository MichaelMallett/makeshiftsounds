<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer;

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
      'db_select', 'db_insert', 'db_update', 'db_merge', 'db_truncate',
    ];

    foreach ($functions as $function) {
      $derivative = $base_definition;

      $derivative['function'] = $function;
      $derivative['description'] = $this->t('Checks for calls to @function() that refer to legacy tables.', [
        '@function' => $function,
      ]);

      $derivatives[$function] = $derivative;
    }

    return $derivatives;
  }

}
