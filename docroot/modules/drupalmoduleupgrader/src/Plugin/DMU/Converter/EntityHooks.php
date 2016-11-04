<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Psr\Log\LoggerInterface;

/**
 * @Converter(
 *  id = "entity_hooks",
 *  description = @Translation("Rewrites various entity-related hooks."),
 *  hook = {
 *    "hook_comment_delete",
 *    "hook_comment_insert",
 *    "hook_comment_presave",
 *    "hook_comment_update",
 *    "hook_node_access",
 *    "hook_node_access_records",
 *    "hook_node_access_records_alter",
 *    "hook_node_delete",
 *    "hook_node_grants",
 *    "hook_node_grants_alter",
 *    "hook_node_insert",
 *    "hook_node_presave",
 *    "hook_node_revision_delete",
 *    "hook_node_search_result",
 *    "hook_node_submit",
 *    "hook_node_update",
 *    "hook_node_update_index",
 *    "hook_node_validate",
 *    "hook_taxonomy_term_delete",
 *    "hook_taxonomy_term_insert",
 *    "hook_taxonomy_term_presave",
 *    "hook_taxonomy_term_update",
 *    "hook_user_delete",
 *    "hook_user_logout"
 *  },
 *  dependencies = { "plugin.manager.drupalmoduleupgrader.rewriter" }
 * )
 */
class EntityHooks extends ConverterBase {

  /**
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $rewriters;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $translator, LoggerInterface $log, PluginManagerInterface $rewriters) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $translator, $log);
    $this->rewriters = $rewriters;
  }

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target, $hook = NULL, $index = 0, $rewriter_id = NULL) {
    $indexer = $target->getIndexer('function');

    if (isset($hook)) {
      if ($indexer->has($hook)) {
        if (empty($rewriter_id)) {
          // Extract the entity type from the hook (e.g. 'hook_node_delete').
          preg_match('/^hook_(.+)_[a-z]+$/', $hook, $matches);
          $rewriter_id = '_rewriter:' . $matches[1];
        }
        $rewriter = $this->rewriters->createInstance($rewriter_id);
        $this->rewriteFunction($rewriter, $indexer->get($hook)->getParameterAtIndex($index), $target);
      }
    }
    else {
      $this->convert($target, 'hook_comment_delete');
      $this->convert($target, 'hook_comment_insert');
      $this->convert($target, 'hook_comment_presave');
      $this->convert($target, 'hook_comment_update');
      $this->convert($target, 'hook_node_access');
      $this->convert($target, 'hook_node_access', 2, '_rewriter:account');
      $this->convert($target, 'hook_node_access_records', 0, '_rewriter:node');
      $this->convert($target, 'hook_node_access_records_alter', 1, '_rewriter:node');
      $this->convert($target, 'hook_node_delete');
      $this->convert($target, 'hook_node_grants', 0, '_rewriter:account');
      $this->convert($target, 'hook_node_grants_alter', 1, '_rewriter:account');
      $this->convert($target, 'hook_node_insert');
      $this->convert($target, 'hook_node_presave');
      $this->convert($target, 'hook_node_revision_delete');
      $this->convert($target, 'hook_node_search_result');
      $this->convert($target, 'hook_node_submit');
      $this->convert($target, 'hook_node_submit', 2, 'form_state');
      $this->convert($target, 'hook_node_update');
      $this->convert($target, 'hook_node_update_index');
      $this->convert($target, 'hook_node_validate');
      $this->convert($target, 'hook_node_validate', 2, 'form_state');
      $this->convert($target, 'hook_taxonomy_term_delete');
      $this->convert($target, 'hook_taxonomy_term_insert');
      $this->convert($target, 'hook_taxonomy_term_presave');
      $this->convert($target, 'hook_taxonomy_term_update');
      $this->convert($target, 'hook_user_delete');
      $this->convert($target, 'hook_user_logout');
    }
  }

}
