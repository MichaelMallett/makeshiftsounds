<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Converter\Routing\RouterInterface.
 */

namespace Drupal\drupalmoduleupgrader\Routing;

/**
 * Defines a collection of routes, each wrapped by an implementation of
 * RouteWrapperInterface in order to normalize the gaping differences
 * between Drupal 7 and Drupal 8 routes.
 */
interface RouterInterface {

  /**
   * Adds a wrapped route definition to this router.
   *
   * @param RouteWrapperInterface $route
   *  The wrapped route definition.
   */
  public function addRoute(RouteWrapperInterface $route);

  /**
   * Completes the 'build' of this router, dispatching the 'router.built'
   * event to all added routes.
   */
  public function finalize();

}
