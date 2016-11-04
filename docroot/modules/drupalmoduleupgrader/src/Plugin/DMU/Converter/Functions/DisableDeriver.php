<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\DeriverBase;

/**
 * Builds derivative definitions for the _disable plugin, based on a bundled configuration
 * file. This allows us (plugin authors) to easily define which function calls can be
 * commented out.
 */
class DisableDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_definition) {
    $derivatives = [];

    $config = \Drupal::config('drupalmoduleupgrader.functions')->get('definitions');
    foreach ($config as $key => $info) {
      // Only disable functions that have been explicitly marked for disabling.
      if (empty($info['disable'])) {
        continue;
      }

      // $key can either be the name of a single function, or an arbitrary string
      // identifying a group of functions to handle.
      if (empty($info['functions'])) {
        $info['functions'] = [$key];
      }

      foreach ($info['functions'] as $function) {
        $derivative = $base_definition;
        $variables = ['@function' => $function . '()'];

        $derivative['function'] = $function;
        $derivative['description'] = $this->t('Disables calls to @function().', $variables);
        if (isset($info['fixme'])) {
          $derivative['fixme'] = $this->t($info['fixme'], $variables);
        }
        $derivative['documentation'] = $info['documentation'];

        $derivatives[$function] = $derivative;
      }
    }

    return $derivatives;
  }

}
