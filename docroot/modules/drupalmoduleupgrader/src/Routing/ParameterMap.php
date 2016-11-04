<?php

namespace Drupal\drupalmoduleupgrader\Routing;

use Doctrine\Common\Collections\ArrayCollection;
use Drupal\drupalmoduleupgrader\Utility\Path\Drupal7\PathComponent;
use Drupal\drupalmoduleupgrader\Utility\Path\Drupal8\PathComponent as PathComponent8x;
use Drupal\drupalmoduleupgrader\Utility\Path\PathUtilityInterface;
use Symfony\Component\Routing\Route as Drupal8Route;

/**
 * Represents a set of parameter bindings for a particular path, callback,
 * and set of arguments.
 */
class ParameterMap extends ArrayCollection {

  /**
   * @var \Drupal\drupalmoduleupgrader\Utility\Path\PathUtilityInterface
   */
  protected $path;

  /**
   * @var integer
   */
  protected $_length = 0;

  protected $bindings = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(PathUtilityInterface $path, array $parameters, array $arguments = []) {
    parent::__construct();
    $this->path = $path;
    $this->_length = sizeof($path);

    while ($parameters) {
      $argument = $arguments ? array_shift($arguments) : ParameterBinding::NO_ARGUMENT;
      $this->addBinding(new ParameterBinding($path, array_shift($parameters), $argument));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $output = [];

    foreach ($this->bindings as $key => $bindings) {
      if (is_integer($key)) {
        /** @var ParameterBinding[] $bindings */
        foreach ($bindings as $binding) {
          $parameter = $binding->getParameter()->getName();
          $function = $binding->getParameter()->getFunction()->getName()->getText();
          $output[$function][$parameter]['name'] = $bindings[0]->getParameter()->getName();

          $value = $bindings[0]->getValue();
          if ($value instanceof PathComponent && $value->isWildcard()) {
            $output[$function][$parameter]['type'] = ltrim($value, '%');
          }
        }
      }
    }

    return $output;
  }

  /**
   * Merge another parameter map into this one. Bindings from the incoming map
   * should 'win', although the specifics are up to the implementing classes.
   *
   * @param ParameterMap $map
   *  The parameter map to merge.
   */
  public function merge(ParameterMap $map) {
    foreach ($map as $binding) {
      $this->addBinding($binding);
    }
  }

  /**
   * Adds a binding to this map, overwriting the existing one if there is a
   * conflict.
   *
   * @param ParameterBinding $binding
   *  The binding to add.
   */
  public function addBinding(ParameterBinding $binding) {
    $value = $binding->getValue();
    // The binding will return a PathComponent if it expects to be physically
    // represented in the path, whether or not it already is.
    if ($value instanceof PathComponent) {
      if ($binding->inPath()) {
        $key = $binding->getArgument();
      }
      else {
        $key = $this->path->indexOf($value);
        if ($key === FALSE) {
          $key = $this->_length++;
        }
      }
    }
    else {
      $key = $binding->getParameter()->getName();
    }

    $this->set($key, $binding);

    if (! isset($this->bindings[$key])) {
      $this->bindings[$key] = [];
    }
    array_unshift($this->bindings[$key], $binding);
  }

  /**
   * Applies the parameter map to a path, modifying it as needed.
   *
   * @param \Drupal\drupalmoduleupgrader\Utility\Path\PathUtilityInterface $path
   *  The path to modify (in-place).
   */
  public function applyPath(PathUtilityInterface $path) {
    foreach ($this as $key => $binding) {
      if (is_integer($key)) {
        $path[$key] = new PathComponent8x('{' . $binding->getParameter()->getName() . '}');
      }
    }
  }

  /**
   * Apply the parameter map to a Drupal 8 route, modifying it as needed.
   *
   * @param \Symfony\Component\Routing\Route $route
   *  The route to process.
   */
  public function applyRoute(Drupal8Route $route) {
    $this->applyPath($this->path);

    foreach ($this as $key => $binding) {
      $parameter = $binding->getParameter();

      /** @var ParameterBinding $binding */
      if (is_integer($key)) {
        if ($parameter->isOptional()) {
          // @todo Don't use eval().
          $value = eval('return ' . $parameter->getValue() . ';');
          $route->setDefault($parameter->getName(), $value);
        }
      }
      elseif ($binding->hasArgument()) {
        $route->setDefault($parameter->getName(), $binding->getValue());
      }
    }
    $route->setPath($this->path->__toString());
  }

}
