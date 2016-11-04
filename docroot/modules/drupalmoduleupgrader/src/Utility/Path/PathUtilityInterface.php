<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Utility\Path\PathUtilityInterface.
 */

namespace Drupal\drupalmoduleupgrader\Utility\Path;

use Doctrine\Common\Collections\Collection as CollectionInterface;

/**
 * Represents a route path.
 */
interface PathUtilityInterface extends CollectionInterface {

  /**
   * Constructs a path utility.
   *
   * @param mixed $path
   *  The path to wrap, either as a string or an array.
   *
   * @throws \InvalidArgumentException
   */
  public function __construct($path);

  /**
   * Returns a new path component wrapping a value.
   *
   * @param mixed $value
   *  The value to wrap.
   *
   * @return \Drupal\drupalmoduleupgrader\Utility\Path\PathComponentInterface
   */
  public static function getComponent($value);

  /**
   * Returns if there are wildcards in the path.
   *
   * @return boolean
   */
  public function hasWildcards();

  /**
   * Returns a PathUtilityInterface for the parent path.
   *
   * @return static
   *
   * @throws \LengthException if the path cannot have a parent (i.e.,
   * the path only has one component).
   */
  public function getParent();

  /**
   * Collapses the path into a string.
   *
   * @return string
   */
  public function __toString();

}
