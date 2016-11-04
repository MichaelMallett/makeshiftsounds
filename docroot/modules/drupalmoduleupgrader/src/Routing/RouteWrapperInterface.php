<?php

namespace Drupal\drupalmoduleupgrader\Routing;

/**
 * Common interface implemented by classes which wrap around Drupal 7 or
 * Drupal 8 routes.
 */
interface RouteWrapperInterface {

  /**
   * Returns an identifier for this route.
   *
   * @return string
   */
  public function getIdentifier();

  /**
   * Returns a PathUtilityInterface implementation for the route.
   *
   * @return \Drupal\drupalmoduleupgrader\Utility\Path\PathUtilityInterface
   */
  public function getPath();

  /**
   * Returns if this route has a parent.
   *
   * @return boolean
   */
  public function hasParent();

  /**
   * Gets the parent route, if there is one. The parent should also be wrapped.
   *
   * @return static|NULL
   */
  public function getParent();

  /**
   * Returns the original, unwrapped route.
   *
   * @return mixed
   */
  public function unwrap();

  /**
   * React to the router (i.e., the collection of routes defined by the
   * module) being completely built.
   *
   * @param RouterBuiltEvent $event
   *  The event object.
   */
  public function onRouterBuilt(RouterBuiltEvent $event);

}
