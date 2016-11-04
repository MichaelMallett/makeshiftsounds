<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Utility\Path\Drupal7\PathUtility.
 */

namespace Drupal\drupalmoduleupgrader\Utility\Path\Drupal7;

use Drupal\drupalmoduleupgrader\Utility\Path\PathUtilityBase;

/**
 * Represents a Drupal 7 route path.
 */
class PathUtility extends PathUtilityBase {

  /**
   * {@inheritdoc}
   */
  public static function getComponent($value) {
    return new PathComponent($value);
  }

  /**
   * Returns if the path has %wildcards or placeholders (%) in it.
   *
   * @return boolean
   */
  public function isDynamic() {
    return ($this->hasWildcards() || $this->hasPlaceholders());
  }

  /**
   * Returns if there are placeholders in the path.
   *
   * @return boolean
   */
  public function hasPlaceholders() {
    return ($this->getPlaceholders()->count() > 0);
  }

  /**
   * Returns every placeholder in the path, keyed by position.
   *
   * @return static
   */
  public function getPlaceholders() {
    return $this->filter(function(PathComponent $component) {
      return $component->isPlaceholder();
    });
  }

  /**
   * Returns a copy of the collection with all placeholders removed.
   *
   * @return static
   */
  public function deletePlaceholders() {
    return $this->filter(function(PathComponent $component) {
      return (! $component->isPlaceholder());
    });
  }

}
