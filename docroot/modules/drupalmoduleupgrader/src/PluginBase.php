<?php

namespace Drupal\drupalmoduleupgrader;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase as CorePluginBase;
use Drupal\Core\StringTranslation\TranslationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for all DMU plugin types, pulling string translation and logging
 * services from the container by default.
 *
 * @deprecated
 */
abstract class PluginBase extends CorePluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var LoggerInterface
   */
  protected $log;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $translator, LoggerInterface $log) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->stringTranslation = $translator;
    $this->log = $log;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $arguments = [
      $configuration,
      $plugin_id,
      $plugin_definition,
      // Always include the string translation and logging services.
      $container->get('string_translation'),
      $container->get('logger.factory')->get('drupalmoduleupgrader'),
    ];

    // Pull any declared dependencies out of the container.
    if (isset($plugin_definition['dependencies'])) {
      foreach ($plugin_definition['dependencies'] as $dependency) {
        $arguments[] = $container->get($dependency);
      }
    }

    return (new \ReflectionClass(get_called_class()))->newInstanceArgs($arguments);
  }

}
