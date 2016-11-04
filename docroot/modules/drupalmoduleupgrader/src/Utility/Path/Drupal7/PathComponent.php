<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Utility\Path\Drupal7\PathComponent.
 */

namespace Drupal\drupalmoduleupgrader\Utility\Path\Drupal7;

use Drupal\drupalmoduleupgrader\Utility\Path\PathComponentBase;

/**
 * Represents a single component in a Drupal 7 route path.
 */
class PathComponent extends PathComponentBase {

  /**
   * Returns if this component is a generic placeholder (%).
   *
   * @return boolean
   */
  public function isPlaceholder() {
    return $this->value == '%';
  }

  /**
   * {@inheritdoc}
   */
  public function isWildcard() {
    return (boolean) preg_match('/%[a-zA-Z0-9_]+/', $this->value);
  }

}
