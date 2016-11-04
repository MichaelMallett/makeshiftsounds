<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\Routing\HookMenu;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Drupal\drupalmoduleupgrader\Utility\Filter\ContainsLogicFilter;
use Pharborist\DocCommentNode;
use Psr\Log\LoggerInterface;

/**
 * @Converter(
 *  id = "routing",
 *  description = @Translation("Converts parts of hook_menu() to the Drupal 8 routing system."),
 *  hook = "hook_menu",
 *  fixme = @Translation("@FIXME
This implementation of hook_menu() cannot be automatically converted because
it contains logic (i.e., branching statements, function calls, object
instantiation, etc.) You will need to convert it manually. Sorry!

For more information on how to convert hook_menu() to Drupal 8's new routing
and linking systems, see https://api.drupal.org/api/drupal/core%21includes%21menu.inc/group/menu/8"),
 *  dependencies = { "plugin.manager.drupalmoduleupgrader.route" }
 * )
 */
class Routing extends ConverterBase {

  /**
   * The route converters' plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $routeConverters;

  /**
   * Constructs a Routing object.
   *
   * @param array $configuration
   *   Additional configuration for the plugin.
   * @param string $plugin_id
   *   The plugin ID, will be "Links".
   * @param mixed $plugin_definition
   *   The plugin definition as derived from the annotations.
   *
   * @param PluginManagerInterface $route_converters
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $translator, LoggerInterface $log, PluginManagerInterface $route_converters) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $translator, $log);
    $this->routeConverters = $route_converters;
  }

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    // If the hook implementation contains logic, we cannot convert it and
    // that's that. So we'll leave a FIXME and bail out.
    /** @var \Pharborist\Functions\FunctionDeclarationNode $hook */
    $hook = $target->getIndexer('function')->get('hook_menu');
    if ($hook->is(new ContainsLogicFilter)) {
      $hook->setDocComment(DocCommentNode::create($this->pluginDefinition['fixme']));
      $target->save($hook);
      return;
    }

    $hook_menu = new HookMenu($target, $this->routeConverters);
    foreach ($hook_menu->getSourceRoutes() as $path => $route) {
      /** @var \Drupal\drupalmoduleupgrader\Routing\Drupal7\RouteWrapper $route */
      if ($route->containsKey('page callback')) {
        $plugin_id = $this->routeConverters->hasDefinition($route['page callback']) ? $route['page callback'] : 'default';
        /** @var \Drupal\drupalmoduleupgrader\Routing\RouteConverterInterface $converter */
        $this->routeConverters->createInstance($plugin_id)->buildRoute($target, $route);
      }
    }

    $routing = [];
    foreach ($hook_menu->getDestinationRoutes() as $name => $route) {
      $routing[$name] = [
        'path' => $route->getPath()->__toString(),
        'defaults' => $route->getDefaults(),
        'requirements' => $route->getRequirements(),
      ];
    }
    $this->writeInfo($target, 'routing', $routing);
  }

}
