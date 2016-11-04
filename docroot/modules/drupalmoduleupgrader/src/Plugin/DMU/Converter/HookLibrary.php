<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * @Converter(
 *  id = "hook_library",
 *  description = @Translation("Converts Drupal 7's hook_library() to YAML."),
 *  hook = "hook_library"
 * )
 */
class HookLibrary extends ConverterBase {

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    try {
      $libraries = $this->executeHook($target, $this->pluginDefinition['hook']);
    }
    catch (\LogicException $e) {
      return;
    }

    foreach ($libraries as $id => &$lib) {
      if (isset($lib['website'])) {
        $lib['remote'] = $lib['website'];
        unset($lib['website']);
      }

      if (isset($lib['dependencies'])) {
        $lib['dependencies'] = array_map(function(array $dependency) {
          if ($dependency[0] == 'system') {
            $dependency[0] == 'core';
          }
          return implode('/', $dependency);
        }, $lib['dependencies']);
      }
    }

    $this->writeInfo($target, 'libraries', $libraries);
  }

}
