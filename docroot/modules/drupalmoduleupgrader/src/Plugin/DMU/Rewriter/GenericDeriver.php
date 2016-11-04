<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Rewriter;

use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds derivative definitions for the generic rewriter, based on the
 * drupalmoduleupgrader.rewriters configuration object.
 */
class GenericDeriver implements ContainerDeriverInterface {

  /**
   * @var array
   */
  protected $config;

  public function __construct(array $config) {
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('config.factory')->get('drupalmoduleupgrader.rewriters')->get('definitions')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinition($derivative_id, $base_definition) {
    $derivatives = $this->getDerivativeDefinitions($base_definition);

    if (isset($derivatives[$derivative_id])) {
      return $derivatives[$derivative_id];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_definition) {
    $derivatives = [];

    foreach ($this->config as $data_type => $definition) {
      $derivatives[$data_type] = $definition + $base_definition;
    }

    return $derivatives;
  }

}
