<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Routing;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\Routing\Drupal7\RouteWrapper as Drupal7Route;
use Drupal\drupalmoduleupgrader\Routing\Drupal8\RouteWrapper as Drupal8Route;
use Drupal\drupalmoduleupgrader\Routing\ParameterMap;
use Drupal\drupalmoduleupgrader\Routing\RouteConverterInterface;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Drupal\drupalmoduleupgrader\Utility\StringTransformTrait;
use Pharborist\ControlStructures\ReturnStatementNode;
use Pharborist\Filter;
use Pharborist\Functions\ParameterNode;
use Pharborist\Objects\ClassMethodCallNode;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Route as CoreRoute;

/**
 * @Converter(
 *  id = "default",
 *  description = @Translation("Converts a menu item to a _controller route."),
 *  dependencies = { "router.route_provider", "plugin.manager.drupalmoduleupgrader.rewriter" }
 * )
 */
class ContentRoute extends ConverterBase implements RouteConverterInterface, ContainerFactoryPluginInterface {

  use StringTransformTrait;

  /**
   * @var RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * @var PluginManagerInterface
   */
  protected $rewriters;

  /**
   * Constructs a RouteConverterBase object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $translator, LoggerInterface $log, RouteProviderInterface $route_provider, PluginManagerInterface $rewriters) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $translator, $log);
    $this->routeProvider = $route_provider;
    $this->rewriters = $rewriters;
  }

  /**
   * Conform with ConverterInterface, which we implement through ConverterBase.
   * Because route conversion is so complex, the Routing plugin never calls
   * this method. It relies instead on the other methods defined in
   * RouteConverterInterface.
   */
  final public function convert(TargetInterface $target) {}

  /**
   * {@inheritdoc}
   */
  public function getName(TargetInterface $target, Drupal7Route $route) {
    $name = $target->id() . '.' . $this->unPrefix($route['page callback'], $target->id());

    $arguments = array_filter($route['page arguments'], 'is_string');
    if ($arguments) {
      $name .= '_' . implode('_', $arguments);
    }

    return $name;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPath(TargetInterface $target, Drupal7Route $route) {
    // The parameter map modifies the path in-place, so we'll clone it in order
    // to keep this method non-destructive.
    $path = clone $route->getPath();
    $this->buildParameterMap($target, $route)->applyPath($path);
    return $path;
  }

  /**
   * Builds a parameter map from the aggregated arguments of the title,
   * access, and page callbacks.
   *
   * @return \Drupal\drupalmoduleupgrader\Routing\ParameterMap
   */
  protected function buildParameterMap(TargetInterface $target, Drupal7Route $route) {
    $map = new ParameterMap(clone $route->getPath(), []);

    $indexer = $target->getIndexer('function');

    if ($indexer->has($route['title callback'])) {
      $map->merge(new ParameterMap(
        $route->getPath(),
        $indexer->get($route['title callback'])->getParameters()->toArray(),
        $route['title arguments']
      ));
    }

    if ($indexer->has($route['access callback'])) {
      $map->merge(new ParameterMap(
        $route->getPath(),
        $indexer->get($route['access callback'])->getParameters()->toArray(),
        $route['access arguments']
      ));
    }

    if ($indexer->has($route['page callback'])) {
      $map->merge(new ParameterMap(
        $route->getPath(),
        $indexer->get($route['page callback'])->getParameters()->toArray(),
        $route['page arguments']
      ));
    }

    return $map;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRouteDefinition(TargetInterface $target, Drupal7Route $route) {
    $indexer = $target->getIndexer('function');

    $definition = new CoreRoute('');
    $this->buildParameterMap($target, $route)->applyRoute($definition);

    $controller = $this->getController($target, $route)->getName()->getAbsolutePath();

    if ($route->containsKey('title')) {
      $definition->setDefault('_title', $route['title']);
    }
    elseif ($indexer->has($route['title callback'])) {
      $definition->setDefault('_title_callback', $controller . '::' . $route['title callback']);
    }

    if ($route->isAbsoluteAccess()) {
      $definition->setRequirement('_access', $route['access callback'] ? 'true' : 'false');
    }
    elseif ($route->isPermissionBased()) {
      $definition->setRequirement('_permission', $route['access arguments'][0]);
    }
    elseif ($indexer->has($route['access callback'])) {
      $definition->setRequirement('_custom_access', $controller . '::' . $route['access callback']);
    }

    if ($indexer->has($route['page callback'])) {
      $definition->setDefault('_controller', $controller . '::' . $route['page callback']);
    }

    return new Drupal8Route($this->getName($target, $route), $definition, $this->routeProvider);
  }

  /**
   * {@inheritdoc}
   */
  public function buildRoute(TargetInterface $target, Drupal7Route $route) {
    $definition = $this->buildRouteDefinition($target, $route);

    $map = $this->buildParameterMap($target, $route);
    $map->applyRoute($definition->unwrap());

    $indexer = $target->getIndexer('function');

    foreach ($map->toArray() as $function_name => $parameters) {
      if ($parameters && $indexer->has($function_name)) {
        /** @var \Pharborist\Functions\FunctionDeclarationNode $function */
        $function = $indexer->get($function_name);
        foreach ($parameters as $parameter_name => $info) {
          $parameter = $function->getParameterByName($parameter_name)->setName($info['name'], TRUE);
          if (isset($info['type'])) {
            $plugin_id = '_rewriter:' . $info['type'];
            if ($this->rewriters->hasDefinition($plugin_id)) {
              $this->rewriters->createInstance($plugin_id)->rewrite($parameter);
            }
          }
        }
      }
    }

    $class_indexer = $target->getIndexer('class');
    if ($class_indexer->has('DefaultController')) {
      $controller = $class_indexer->get('DefaultController');
    }
    else {
      $controller = $this->getController($target, $route);
      $class_indexer->addFile($this->writeClass($target, $controller));
    }

    if ($indexer->has($route['title callback'])) {
      if (! $controller->hasMethod($route['title callback'])) {
        $indexer->get($route['title callback'])->cloneAsMethodOf($controller);
      }
    }

    if ($indexer->has($route['access callback'])) {
      $func = $indexer->get($route['access callback']);

      $returns = $func->find(Filter::isInstanceOf('\Pharborist\ReturnStatementNode'));
      foreach ($returns as $ret) {
        $call = ClassMethodCallNode::create('\Drupal\Core\Access\AccessResult', 'allowedIf')->appendArgument($ret->getExpression());
        $ret->replaceWith(ReturnStatementNode::create($call));
      }

      // The access callback always receives an $account parameter.
      if ($func->hasParameter('account')) {
        $func->getParameter('account')->setTypeHint('Drupal\Core\Session\AccountInterface');
      }
      else {
        $account = ParameterNode::create('account')->setTypeHint('Drupal\Core\Session\AccountInterface');
        $func->appendParameter($account);
      }

      if (! $controller->hasMethod($route['access callback'])) {
        $func->cloneAsMethodOf($controller);
      }
    }

    if ($indexer->has($route['page callback'])) {
      if (! $controller->hasMethod($route['page callback'])) {
        $indexer->get($route['page callback'])->cloneAsMethodOf($controller);
      }
    }

    $this->writeClass($target, $controller);
  }

  protected function getController(TargetInterface $target, Drupal7Route $route) {
    $render = [
      '#theme' => 'dmu_controller',
      '#module' => $target->id(),
    ];
    return $this->parse($render);
  }

}
