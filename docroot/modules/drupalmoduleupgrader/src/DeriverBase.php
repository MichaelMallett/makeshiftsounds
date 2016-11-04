<?php

namespace Drupal\drupalmoduleupgrader;

use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for DMU's plugin derivers. Sets up the translation service and
 * provides a basic implementation of DeriverInterface::getDerivativeDefinition().
 */
abstract class DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  public function __construct(TranslationInterface $translator) {
    $this->stringTranslation = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static($container->get('string_translation'));
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

}
