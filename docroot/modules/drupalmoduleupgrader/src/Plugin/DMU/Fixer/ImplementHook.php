<?php

/**
 * @file
 * Contains \Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer\ImplementHook.
 */

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Fixer;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\drupalmoduleupgrader\FixerBase;
use Pharborist\DocCommentNode;
use Pharborist\Functions\FunctionDeclarationNode;
use Pharborist\Functions\ParameterNode;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Fixer(
 *  id = "implement_hook"
 * )
 */
class ImplementHook extends FixerBase implements ContainerFactoryPluginInterface {

  protected $moduleHandler;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $this->moduleHandler->loadInclude($this->configuration['module'], 'php', 'api');

    $hook = $this->configuration['hook'];
    $function = FunctionDeclarationNode::create($this->target->id() . '_' . $hook);
    $function->setDocComment(DocCommentNode::create('Implements hook_' . $hook));

    $reflector = new \ReflectionFunction('hook_' . $hook);
    $function->matchReflector($reflector);

    $module = $this->target->getPath('.module');
    $doc = $this->target->open($module)->append($function);
    $this->target->save($doc);
  }

}
