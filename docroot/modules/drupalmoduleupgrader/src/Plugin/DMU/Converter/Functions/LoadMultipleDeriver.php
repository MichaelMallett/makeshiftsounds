<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\DeriverBase;

/**
 * Builds derivative definitions for the _load_multiple plugin.
 */
class LoadMultipleDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_definition) {
    $derivatives = [];

    foreach (['node', 'user', 'comment', 'taxonomy_term'] as $entity_type) {
      $function = $entity_type . '_load_multiple';
      $variables = ['@function' => $function];

      $derivative = $base_definition;
      $derivative['function'] = $function;
      $derivative['entity_type'] = $entity_type;
      $derivative['message'] = $this->t('`@function` is now `EntityStorageInterface::loadMultiple()`.', $variables);
      $derivative['description'] = $this->t('Rewrites calls to @function().', $variables);

      $derivatives[$entity_type] = $derivative;
    }

    return $derivatives;
  }

}
