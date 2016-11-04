<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Utility\Path\Drupal8\PathComponent.
 */

namespace Drupal\drupalmoduleupgrader\Utility\Path\Drupal8;

use Drupal\drupalmoduleupgrader\Utility\Path\PathComponentBase;

/**
 * Represents a single component in a Drupal 8 route path.
 */
class PathComponent extends PathComponentBase {

  /**
   * {@inheritdoc}
   */
  public function isWildcard() {
    return (boolean) preg_match('/\{[a-zA-Z0-9_]+\}/', $this->value);
  }

}
