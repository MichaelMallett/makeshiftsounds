<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\HookToYAML.
 */

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer;

use Drupal\Component\Serialization\Yaml as YAML;
use Drupal\drupalmoduleupgrader\FixerBase;

/**
 * @Fixer(
 *  id = "hook_to_YAML"
 * )
 */
class HookToYAML extends FixerBase {

  public function execute() {
    $destination = $this->getUnaliasedPath($this->configuration['destination']);
    $data = $this->target->executeHook($this->configuration['hook']);
    file_put_contents($destination, YAML::encode($data));
  }

}
