<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Utility\Path\PathComponentBase.
 */

namespace Drupal\drupalmoduleupgrader\Utility\Path;

/**
 * Represents a single component in a route path.
 */
abstract class PathComponentBase implements PathComponentInterface {

  /**
   * @var string
   */
  protected $value;

  /**
   * {@inheritdoc}
   */
  public function __construct($value) {
    $this->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return $this->value;
  }

}
