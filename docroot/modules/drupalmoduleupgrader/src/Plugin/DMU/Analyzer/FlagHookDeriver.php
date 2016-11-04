<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\DeriverBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FlagHookDeriver extends DeriverBase {

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
      $container->get('config.factory')->get('drupalmoduleupgrader.hooks')->get('definitions')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_definition) {
    $derivatives = [];

    foreach ($this->config as $key => $info) {
      if (empty($info['hook'])) {
        $info['hook'] = [$key];
      }

      foreach ($info['hook'] as $hook) {
        $variables = ['@hook' => 'hook_' . $hook . '()'];

        $derivative = array_merge($base_definition, $info);
        $derivative['hook'] = $hook;
        $derivative['message'] = $this->t($info['message'], $variables);
        $derivative['description'] = $this->t('Analyzes implementations of @hook.', $variables);
        foreach ($derivative['documentation'] as &$doc) {
          $doc['title'] = $this->t($doc['title'], $variables);
        }

        $derivatives[$hook] = $derivative;
      }
    }

    return $derivatives;
  }

}
