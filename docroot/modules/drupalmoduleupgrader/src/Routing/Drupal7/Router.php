<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Converter\Routing\Drupal7\Router.
 */

namespace Drupal\drupalmoduleupgrader\Routing\Drupal7;

use Drupal\drupalmoduleupgrader\Routing\RouterBase;

/**
 * Represents a collection of Drupal 7 routes, i.e., the result of hook_menu().
 */
class Router extends RouterBase {

  /**
   * Gets all items of a specific type.
   *
   * @param string $link_types
   *  The link type(s), separated by commas (e.g., 'MENU_NORMAL_ITEM, MENU_LOCAL_TASK').
   *
   * @return static
   */
  public function ofType($link_types) {
    $link_types = array_map('trim', explode(', ', $link_types));

    return $this->filter(function(RouteWrapper $route) use ($link_types) {
      return in_array($route['type'], $link_types);
    });
  }

  /**
   * Gets all items which expose a link of any kind.
   *
   * @return static
   */
  public function getAllLinks() {
    return $this->filter(function(RouteWrapper $route) {
      return $route->hasLink();
    });
  }

  /**
   * Gets all normal links.
   *
   * @return static
   */
  public function getLinks() {
    return $this->filter(function(RouteWrapper $route) {
      return $route->isLink();
    });
  }

  /**
   * Gets all local tasks.
   *
   * @return static
   */
  public function getLocalTasks() {
    return $this->filter(function(RouteWrapper $route) {
      return $route->isLocalTask();
    });
  }

  /**
   * Gets all default local tasks.
   *
   * @return static
   */
  public function getDefaultLocalTasks() {
    return $this->filter(function(RouteWrapper $route) {
      return $route->isDefaultLocalTask();
    });
  }

  /**
   * Gets all local actions.
   *
   * @return static
   */
  public function getLocalActions() {
    return $this->filter(function(RouteWrapper $route) {
      return $route->isLocalAction();
    });
  }

  /**
   * Gets all contextual links.
   *
   * @return static
   */
  public function getContextualLinks() {
    return $this->filter(function(RouteWrapper $route) {
      return $route->isContextualLink();
    });
  }

}
