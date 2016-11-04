<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\ParameterNode;
use Psr\Log\LoggerInterface;

/**
 * @Converter(
 *  id = "hook_ENTITY_TYPE_view",
 *  description = @Translation("Converts implementations of hook_ENTITY_TYPE_view()."),
 *  hook = {
 *    "hook_comment_view",
 *    "hook_node_view",
 *    "hook_taxonomy_term_view",
 *    "hook_user_view"
 *  },
 *  dependencies = { "plugin.manager.drupalmoduleupgrader.rewriter" }
 * )
 */
class HookEntityTypeView extends ConverterBase {

  /**
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $rewriters;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $translator, LoggerInterface $log, PluginManagerInterface $rewriters) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $translator, $log);
    $this->rewriters = $rewriters;
  }

  public function convert(TargetInterface $target) {
    $indexer = $target->getIndexer('function');

    $hooks = array_filter($this->pluginDefinition['hook'], [$indexer, 'has']);
    foreach ($hooks as $hook) {
      /** @var \Pharborist\Functions\FunctionDeclarationNode $function */
      $function = $indexer->get($hook);
      $function->prependParameter(ParameterNode::create('build')->setTypeHint('array')->setReference(TRUE));

      // Extract the entity type from the hook name (e.g. 'hook_node_view').
      preg_match('/^hook_(.+)_view$/', $hook, $matches);
      $entity_type = $matches[1];
      $rewriter = $this->rewriters->createInstance('_rewriter:' . $entity_type);
      $this->rewriteFunction($rewriter, $function->getParameterAtIndex(1), $target);
    }
  }

}
