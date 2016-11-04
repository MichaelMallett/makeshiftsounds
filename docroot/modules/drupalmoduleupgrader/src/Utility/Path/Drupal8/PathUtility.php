<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Utility\Path\PathUtilityBase.
 */

namespace Drupal\drupalmoduleupgrader\Utility\Path\Drupal8;

use Drupal\drupalmoduleupgrader\Utility\Path\PathUtilityBase;

/**
 * Represents a Drupal 8 route path.
 */
class PathUtility extends PathUtilityBase {

  /**
   * {@inheritdoc}
   */
  public static function getComponent($value) {
    return new PathComponent($value);
  }

}
