<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Routing;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\Routing\Drupal7\RouteWrapper;
use Drupal\drupalmoduleupgrader\Routing\ParameterMap;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Drupal\drupalmoduleupgrader\Utility\FormConverterFactory;
use Psr\Log\LoggerInterface;

/**
 * @Converter(
 *  id = "drupal_get_form",
 *  description = @Translation("Converts a drupal_get_form() menu item to a _form route."),
 *  dependencies = { "router.route_provider", "plugin.manager.drupalmoduleupgrader.rewriter", "drupalmoduleupgrader.form_converter" }
 * )
 */
class FormRoute extends ContentRoute {

  /**
   * @var \Drupal\drupalmoduleupgrader\Utility\FormConverter
   */
  protected $formConverter;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $translator, LoggerInterface $log, RouteProviderInterface $route_provider, PluginManagerInterface $rewriters, FormConverterFactory $form_converter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $translator, $log, $route_provider, $rewriters);
    $this->formConverter = $form_converter;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(TargetInterface $target, RouteWrapper $route) {
    $name = $target->id() . '.' . $this->unPrefix($route['page arguments'][0], $target->id());

    $arguments = array_filter(array_slice($route['page arguments'], 1), 'is_string');
    if ($arguments) {
      $name .= '_' . implode('_', $arguments);
    }

    return $name;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildParameterMap(TargetInterface $target, RouteWrapper $route) {
    $map = parent::buildParameterMap($target, $route);

    $indexer = $target->getIndexer('function');
    if ($indexer->has($route['page arguments'][0])) {
      $builder = $indexer->get($route['page arguments'][0]);
      $parameters = $this->bumpKeys(array_slice($builder->getParameters()->toArray(), 2), 2);
      $arguments = $this->bumpKeys(array_slice($route['page arguments'], 1), 2);
      $map->merge(new ParameterMap($route->getPath(), $parameters, $arguments));
    }

    return $map;
  }

  /**
   * Returns a copy of the input array with the keys increased by $offset. This
   * only works on numerically indexed arrays; I don't know what it does to
   * associative arrays, but probably nothing good.
   *
   * @param array $input
   *  The input array.
   *
   * @param int $offset
   *  The offset to add to the keys.
   *
   * @return array
   */
  private function bumpKeys(array $input, $offset = 0) {
    $output = [];

    foreach ($input as $key => $value) {
      $output[ $key + $offset ] = $value;
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRoute(TargetInterface $target, RouteWrapper $route) {
    $controller = $this->formConverter->get($target, $route['page arguments'][0])->build();
    $target->getIndexer('class')->addFile($this->writeClass($target, $controller));
  }

  protected function getController(TargetInterface $target, RouteWrapper $route) {
    return $this->formConverter->get($target, $route['page arguments'][0])->render();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRouteDefinition(TargetInterface $target, RouteWrapper $route) {
    $definition = parent::buildRouteDefinition($target, $route);
    $definition->setDefault('_form', $this->getController($target, $route)->getName()->getAbsolutePath());

    return $definition;
  }

}
