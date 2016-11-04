<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\DeriverBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds derivative definitions for the _entity_operation plugin, allowing us to
 * rewrite calls to things like entity_save(), node_delete(), entity_label(), etc.
 */
class EntityOperationDeriver extends DeriverBase {

  /**
   * @var array
   */
  protected $config;

  public function __construct(TranslationInterface $translator, array $config) {
    parent::__construct($translator);
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('string_translation'),
      $container->get('config.factory')->get('drupalmoduleupgrader.entity_operations')->get('definitions')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_definition) {
    $derivatives = [];

    foreach ($this->config as $entity_type => $operations) {
      foreach ($operations as $operation) {
        $function = $entity_type . '_' . $operation;
        $variables = [
          '@function' => $function,
          '@operation' => $operation,
        ];
        $derivative = $base_definition;
        $derivative['function'] = $function;
        $derivative['method'] = $operation;
        $derivative['message'] = $this->t('`@function` is now `EntityInterface::@operation`.', $variables);
        $derivative['description'] = $this->t('Rewrites calls to @function().', $variables);
        $derivatives[$function] = $derivative;
      }
    }

    return $derivatives;
  }

}
