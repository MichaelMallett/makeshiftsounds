<?php

namespace Drupal\drupalmoduleupgrader\Routing;

use Drupal\drupalmoduleupgrader\Utility\Path\Drupal7\PathComponent;
use Drupal\drupalmoduleupgrader\Utility\Path\PathUtilityInterface;
use Pharborist\Functions\ParameterNode;
use Pharborist\Types\ScalarNode;

/**
 * Represents a binding between a single callback parameter and a single
 * path component in a Drupal 8 route path, possibly affected by an argument.
 */
class ParameterBinding {

  /**
   * @var PathUtilityInterface
   */
  protected $path;

  /**
   * @var ParameterNode
   */
  protected $parameter;

  /**
   * @var mixed
   */
  protected $argument;

  /**
   * The trouble with Drupal 7 callback arguments is that virtually any value
   * could be explicitly passed, including NULL and FALSE. -1 is an illegal
   * value because it's an integer, but not a valid path position. So we'll
   * use it here as a signal that no argument is explicitly bound to the
   * parameter.
   */
  const NO_ARGUMENT = -1;

  public function __construct(PathUtilityInterface $path, ParameterNode $parameter, $argument = self::NO_ARGUMENT) {
    // Clone $path so that we have our own copy to look at. The original $path
    // is (probably) modified by upstream code.
    $this->path = clone $path;
    $this->parameter = $parameter;
    $this->argument = $argument;
  }

  /**
   * The original parameter node.
   *
   * @return \Pharborist\Functions\ParameterNode
   */
  public function getParameter() {
    return $this->parameter;
  }

  /**
   * Returns if the parameter is explicitly represented in the path.
   *
   * @return boolean
   */
  public function inPath() {
    return ($this->isPathPosition() && sizeof($this->path) > $this->getArgument());
  }

  /**
   * Returns if this binding has an explicit argument.
   *
   * @return boolean
   */
  public function hasArgument() {
    return ($this->getArgument() !== self::NO_ARGUMENT);
  }

  /**
   * Returns the argument.
   *
   * @return mixed
   */
  public function getArgument() {
    return $this->argument;
  }

  /**
   * Whether or not the argument is a path position (integer greater
   * than or equal to 0).
   *
   * @return boolean
   */
  public function isPathPosition() {
    return ($this->hasArgument() && is_integer($this->getArgument()));
  }

  /**
   * Returns the value of the binding. If the value is an instance of
   * \Drupal\drupalmoduleupgrader\Utility\Path\PathComponentInterface,
   * the binding expects to be physically represented in the path, although
   * it may not yet be (this can be ascertained by the inPath() method). Any
   * other value is used verbatim.
   *
   * @return mixed
   */
  public function getValue() {
    if ($this->hasArgument()) {
      if ($this->isPathPosition()) {
        $position = $this->getArgument();
        return $this->path->containsKey($position) ? $this->path[$position] : new PathComponent('%');
      }
      else {
        return $this->getArgument();
      }
    }
    else {
      $value = $this->getParameter()->getValue();

      if ($value instanceof ScalarNode) {
        return $value->toValue();
      }
    }
  }

}
