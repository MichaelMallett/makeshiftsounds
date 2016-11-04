<?php

namespace Drupal\drupalmoduleupgrader;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a default implementation of ContainerFactoryPluginInterface which
 * will pull any dependencies declared in the plugin definition out of the
 * container.
 */
trait DependencyCollectorTrait {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $arguments = array_slice(func_get_args(), 1);
    $arguments += array_map([ $container, 'get' ], @($plugin_definition['dependencies'] ? : []));
    return (new \ReflectionClass(get_called_class()))->newInstanceArgs($arguments);
  }

}
