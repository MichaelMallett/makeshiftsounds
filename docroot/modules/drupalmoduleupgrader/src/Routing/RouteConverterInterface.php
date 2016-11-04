<?php

namespace Drupal\drupalmoduleupgrader\Routing;

use Drupal\drupalmoduleupgrader\Routing\Drupal7\RouteWrapper;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * Defines a route converter, which converts a Drupal 7 router item to a
 * Drupal 8 Symfony route. These plugins are NOT responsible for converting
 * *links* (including tabs or local actions), only the actual route.
 *
 * Every method in this interface takes two arguments. First, a TargetInterface
 * representing the target module. Second, the original Drupal 7 route (i.e.,
 * hook_menu() item), wrapped in a RouteWrapperInterface.
 */
interface RouteConverterInterface {

  /**
   * Generates the route's machine-readable name.
   *
   * @return string
   */
  public function getName(TargetInterface $target, RouteWrapper $route);

  /**
   * Builds the Drupal 8 path for the route.
   *
   * The path should be prefixed by a slash, and contain {slugs} corresponding
   * to parameters of the callback method which can accept input from the path.
   * Parameters are matched to slugs by name and type hint.
   *
   * @return \Drupal\drupalmoduleupgrader\Utility\Path\PathUtilityInterface
   */
  public function buildPath(TargetInterface $target, RouteWrapper $route);

  /**
   * Builds the Drupal 8 definition for the route, without making any changes
   * to the original module or callback.
   *
   * @return Drupal8\RouteWrapper
   */
  public function buildRouteDefinition(TargetInterface $target, RouteWrapper $route);

  /**
   * Builds the Drupal 8 route, making any needed changes to the original module
   * and/or callback.
   */
  public function buildRoute(TargetInterface $target, RouteWrapper $route);

}
