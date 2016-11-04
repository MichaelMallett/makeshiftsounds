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
 *  id = "hook_node_prepare",
 *  description = @Translation("Converts hook_node_prepare() into hook_ENTITY_TYPE_prepare_form()."),
 *  hook = "hook_node_prepare",
 *  dependencies = { "plugin.manager.drupalmoduleupgrader.rewriter" }
 * )
 */
class HookNodePrepare extends ConverterBase {

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
  public function convert(TargetInterface $target) {
    /** @var \Pharborist\Functions\FunctionDeclarationNode $function */
    $function = $target->getIndexer('function')->get('hook_node_prepare');

    // foo_node_prepare() --> foo_node_prepare_form().
    $function->setName($function->getName() . '_form');

    // The first parameter is a node, so rewrite the function accordingly.
    $this->rewriters
      ->createInstance('_entity:node')
      ->rewrite($function->getParameterAtIndex(0));

    // Create the $operation parameter.
    $function->appendParameter(ParameterNode::create('operation'));

    // Create the $form_state parameter.
    $form_state = ParameterNode::create('form_state')->setTypeHint('\Drupal\Core\Form\FormStateInterface');
    $function->appendParameter($form_state);

    $target->save($function);
  }

}
