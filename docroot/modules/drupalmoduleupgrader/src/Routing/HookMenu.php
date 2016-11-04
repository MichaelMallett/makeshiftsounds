<?php

namespace Drupal\drupalmoduleupgrader\Routing;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\drupalmoduleupgrader\Routing\Drupal7\Router as Drupal7Router;
use Drupal\drupalmoduleupgrader\Routing\Drupal7\RouteWrapper as Drupal7Route;
use Drupal\drupalmoduleupgrader\Routing\RouterBase as Drupal8Router;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * This class is a conversion map for hook_menu().
 *
 * It will compile a router object for hook_menu(), and resolve the inherent
 * hierarchies where possible. It will also build the corresponding Drupal 8
 * routes by invoking the appropriate route converters.
 *
 * All this is absolutely READ-ONLY. Nothing in the target module is changed.
 */
class HookMenu {

  /**
   * The source routes (from Drupal 7).
   *
   * @var RouterInterface
   */
  protected $sourceRoutes;

  /**
   * The destination routes (as in a routing.yml file).
   *
   * @var RouterInterface
   */
  protected $destinationRoutes;

  /**
   * Maps Drupal 7 paths to Drupal 8 route names.
   *
   * @var string[]
   */
  protected $routeMap = [];

  /**
   * @var \Drupal\drupalmoduleupgrader\TargetInterface
   */
  protected $target;

  /**
   * The route converters' plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $routeConverters;

  /**
   * Constructs a HookMenu object.
   *
   * @param \Drupal\drupalmoduleupgrader\TargetInterface $target
   *  The target module.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $route_converters
   *   The route converters.
   */
  public function __construct(TargetInterface $target, PluginManagerInterface $route_converters) {
    $this->target = $target;
    $this->routeConverters = $route_converters;

    // If the hook_menu() implementation doesn't exist, get the implementation
    // from the indexer and eval it into existence. It's the calling code's
    // responsibility to ensure that the implementation doesn't contain anything
    // which will blow up on execution.
    $hook = $target->id() . '_menu';
    if (! function_exists($hook)) {
      eval($target->getIndexer('function')->get('hook_menu')->getText());
    }
  }

  /**
   * Returns the collection of routes in the source.
   *
   * @return RouterInterface
   *   The requested link collection.
   */
  public function getSourceRoutes() {
    if (empty($this->sourceRoutes)) {
      $this->sourceRoutes = new Drupal7Router();

      $items = call_user_func($this->target->id() . '_menu');
      foreach ($items as $path => $item) {
        $this->sourceRoutes->addRoute(new Drupal7Route($path, $item));
      }

      // Now that all routes have been loaded, tell them to resolve their
      // hierarchical relationships.
      $this->sourceRoutes->finalize();
    }
    return $this->sourceRoutes;
  }

  /**
   * Returns the collection of routes in the destination.
   *
   * @return RouterInterface
   *   The requested route collection.
   */
  public function getDestinationRoutes() {
    if (empty($this->destinationRoutes)) {
      $this->destinationRoutes = $this->buildDestinationRoutes();
    }
    return $this->destinationRoutes;
  }

  /**
   * Returns the destination route for the given source path.
   *
   * @param string $path
   *   The source path, as defined in hook_menu().
   *
   * @return \Drupal\drupalmoduleupgrader\Routing\Drupal8\RouteWrapper|NULL
   *   The destination route.
   */
  public function getDestinationRoute($path) {
    return $this->getDestinationRoutes()->get($this->routeMap[$path]);
  }

  /**
   * Builds the Drupal 8 router by running the Drupal 7 router items through
   * the appropriate route converters.
   *
   * @return RouterInterface
   */
  private function buildDestinationRoutes() {
    // @todo These are currently hardcoded on the D7 -> D8 conversion. Make this
    //   configurable.
    $router = new Drupal8Router();
    $this->routeMap = [];

    foreach ($this->getSourceRoutes() as $path => $route) {
      /** @var Drupal7\RouteWrapper $route */
      // If the route hasn't got a page callback...don't even try.
      if (!$route->containsKey('page callback')) {
        continue;
      }

      // Get the appropriate route converter, which will build the route
      // definition.
      $plugin_id = $route['page callback'];
      if (!$this->routeConverters->hasDefinition($plugin_id)) {
        $plugin_id = 'default';
      }

      /** @var Drupal8\RouteWrapper $d8_route */
      $d8_route = $this->routeConverters->createInstance($plugin_id)->buildRouteDefinition($this->target, $route);
      $router->addRoute($d8_route);
      $this->routeMap[$path] = $d8_route->getIdentifier();
    }
    $router->finalize();

    foreach ($this->getSourceRoutes()->getDefaultLocalTasks() as $path => $route) {
      /** @var Drupal7\RouteWrapper $route */
      if ($route->hasParent()) {
        $parent = (string) $route->getParent()->getPath();
        $this->routeMap[$path] = $this->routeMap[$parent];
      }
    }

    return $router;
  }

}
