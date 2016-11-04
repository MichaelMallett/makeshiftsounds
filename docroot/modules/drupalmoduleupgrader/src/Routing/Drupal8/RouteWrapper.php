<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Converter\Routing\Drupal8\Route.
 */

namespace Drupal\drupalmoduleupgrader\Routing\Drupal8;

use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\drupalmoduleupgrader\Routing\RouterBuiltEvent;
use Drupal\drupalmoduleupgrader\Routing\RouteWrapperInterface;
use Drupal\drupalmoduleupgrader\Utility\Path\Drupal8\PathUtility;
use Symfony\Component\Routing\Route;

/**
 * Wraps around a Symfony Route object, providing helper methods.
 */
class RouteWrapper implements RouteWrapperInterface {

  /**
   * @var string
   */
  protected $name;

  /**
   * @var \Symfony\Component\Routing\Route
   */
  protected $route;

  /**
   * @var \Drupal\drupalmoduleupgrader\Utility\Path\Drupal8\PathUtility
   */
  protected $path;

  /**
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * @var \Symfony\Component\Routing\RouteCollection
   */
  protected $router;

  /**
   * @var static
   */
  protected $parent;

  /**
   * Constructs a Route object.
   */
  public function __construct($name, Route $route, RouteProviderInterface $route_provider) {
    $this->name = $name;
    $this->route = $route;
    $this->routeProvider = $route_provider ? $route_provider: \Drupal::service('router.route_provider');
    $this->path = new PathUtility($route->getPath());
  }

  /**
   * Forwards unknown function calls to the wrapped Route.
   */
  public function __call($method, array $arguments) {
    return call_user_func_array([ $this->route, $method ], $arguments);
  }

  /**
   * {@inheritdoc}
   */
  public function getIdentifier() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * {@inheritdoc}
   */
  public function hasParent() {
    return isset($this->parent);
  }

  /**
   * {@inheritdoc}
   */
  public function getParent() {
    return $this->parent;
  }

  /**
   * {@inheritdoc}
   */
  public function unwrap() {
    return $this->route;
  }

  /**
   * {@inheritdoc}
   */
  public function onRouterBuilt(RouterBuiltEvent $event) {
    $this->router = $event->getRouter();

    try {
      $parent = $this->getPath()->getParent()->__toString();
    }
    catch (\LengthException $e) {
      return;
    }

    // First, search the injected router for the parent route.
    foreach ($this->router as $route) {
      if ($route->getPath() == $parent) {
        $this->parent = $route;
      }
    }

    // Next, search the core route provider if no parent was found.
    if (empty($this->parent)) {
      $parents = $this->routeProvider->getRoutesByPattern($parent)->getIterator();

      if (sizeof($parents) > 0) {
        $this->parent = new static($parents->key(), $parents->current(), $this->routeProvider);
      }
    }
  }

}
