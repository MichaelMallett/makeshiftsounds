<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\Routing\HookMenu;
use Drupal\drupalmoduleupgrader\Routing\LinkBinding\LinkBinding;
use Drupal\drupalmoduleupgrader\Routing\LinkBinding\LinkBindingFactory;
use Drupal\drupalmoduleupgrader\Routing\LinkBinding\LocalActionLinkBinding;
use Drupal\drupalmoduleupgrader\Routing\LinkBinding\LocalTaskLinkBinding;
use Drupal\drupalmoduleupgrader\Routing\LinkBinding\MenuLinkBinding;
use Drupal\drupalmoduleupgrader\Routing\LinkIndex;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Drupal\drupalmoduleupgrader\Utility\Filter\ContainsLogicFilter;
use Pharborist\DocCommentNode;
use Psr\Log\LoggerInterface;

/**
 * @Converter(
 *  id = "links",
 *  description = @Translation("Converts Drupal 7's hook_menu() links to plugin definitions."),
 *  hook = "hook_menu",
 *  fixme = @Translation("@FIXME
This implementation of hook_menu() cannot be automatically converted because
it contains logic (i.e., branching statements, function calls, object
instantiation, etc.) You will need to convert it manually. Sorry!

For more information on how to convert hook_menu() to Drupal 8's new routing
and linking systems, see https://api.drupal.org/api/drupal/core%21includes%21menu.inc/group/menu/8"),
 *  dependencies = { "plugin.manager.drupalmoduleupgrader.route", "drupalmoduleupgrader.link_binding" }
 * )
 */
class Links extends ConverterBase {

  /**
   * @var PluginManagerInterface
   */
  protected $routeConverters;

  /**
   * @var LinkBindingFactory
   */
  protected $linkBinding;

  /**
   * Constructs a Links object.
   *
   * @param array $configuration
   *   Additional configuration for the plugin.
   * @param string $plugin_id
   *   The plugin ID, will be "Links".
   * @param string $plugin_definition
   *   The plugin definition as derived from the annotations.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $route_converters
   *  The plugin manager for route converters, used by HookMenu.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $translator, LoggerInterface $log, PluginManagerInterface $route_converters, LinkBindingFactory $link_binding) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $translator, $log);
    $this->routeConverters = $route_converters;
    $this->linkBinding = $link_binding;
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

    // Links are split out by group because there are separate config files
    // for each link type.
    $links = [
      'menu' => new LinkIndex(),
      'task' => new LinkIndex(),
      'action' => new LinkIndex(),
      'contextual' => new LinkIndex(),
    ];

    $hook_menu = new HookMenu($target, $this->routeConverters);
    foreach ($hook_menu->getSourceRoutes()->getAllLinks() as $path => $source) {
      /** @var LinkBinding $binding */
      $binding = $this->linkBinding->create($source, $hook_menu->getDestinationRoute($path));

      // Skip if the converter wasn't able to find a destination.
      $destination = $binding->getDestination();
      if (empty($destination)) {
        continue;
      }

      if ($binding instanceof MenuLinkBinding) {
        $links['menu']->addBinding($binding);
      }
      elseif ($binding instanceof LocalTaskLinkBinding) {
        $links['task']->addBinding($binding);
      }
      elseif ($binding instanceof LocalActionLinkBinding) {
        $links['action']->addBinding($binding);
      }
      elseif ($source->isContextualLink()) {
        $links['contextual']->addBinding($binding);
      }
    }

    $links = array_map(function(LinkIndex $index) {
      return $index->build();
    }, $links);

    foreach ($links['contextual'] as $link) {
      $link['group'] = $target->id();
    }

    foreach ($links as $group => $data) {
      if ($data) {
        $this->writeInfo($target, 'links.' . $group, $data);
      }
    }
  }

}
