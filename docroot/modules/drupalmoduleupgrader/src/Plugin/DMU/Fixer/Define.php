<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\Define.
 */

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer;

use Drupal\Component\Serialization\Yaml as YAML;
use Drupal\Component\Utility\NestedArray;
use Drupal\drupalmoduleupgrader\FixerBase;

/**
 * @Fixer(
 *  id = "define"
 * )
 */
class Define extends FixerBase {

  public function execute() {
    $file = $this->getUnaliasedPath($this->configuration['in']);
    $data = file_exists($file) ? YAML::decode(file_get_contents($file)) : [];
    $keys = explode('/', $this->configuration['key']);
    NestedArray::setValue($data, $keys, $this->configuration['value']);
    file_put_contents($file, YAML::encode($data));
  }

}
