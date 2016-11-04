<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;
use Pharborist\Types\StringNode;
use Psr\Log\LoggerInterface;

/**
 * @Converter(
 *  id = "url",
 *  description = @Translation("Rewrites calls to url()."),
 *  fixme = @Translation("url() expects a route name or an external URI."),
 *  dependencies = { "router.route_provider" }
 * )
 */
class URL extends FunctionCallModifier implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $translator, LoggerInterface $log, RouteProviderInterface $route_provider) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $translator, $log);
    $this->routeProvider = $route_provider;
  }

  /**
   * Looks up routes by path, and returns TRUE if at least one was found.
   *
   * @param string $path
   *  The path to search for, not including the leading slash. Can be an
   *  external URL.
   *
   * @return boolean
   *  TRUE if the path matches a route, FALSE otherwise. External URLs will
   *  always return FALSE.
   */
  protected function routeExists($path) {
    // If there's a scheme in the URL, consider this an external URL and don't even
    // try to rewrite it.
    $scheme = parse_url($path, PHP_URL_SCHEME);
    if (isset($scheme)) {
      return FALSE;
    }
    else {
      $routes = $this->routeProvider->getRoutesByPattern('/' . $path);
      return (sizeof($routes) > 0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();
    if ($arguments[0] instanceof StringNode) {
      $path = $arguments[0]->toValue();

      // If the URL has a scheme (e.g., http://), it's external.
      if (parse_url($path, PHP_URL_SCHEME)) {
        return ClassMethodCallNode::create('\Drupal\Core\Url', 'fromUri')
          ->appendArgument(clone $arguments[0]);
      }
      elseif ($this->routeExists($path)) {
        $route = $this->routeProvider->getRoutesByPattern('/' . $path)->getIterator()->key();
        return ClassMethodCallNode::create('\Drupal\Core\Url', 'fromRoute')
          ->appendArgument(StringNode::fromValue($route));
      }
    }
  }

}
