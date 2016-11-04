<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Analyzer;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\DeriverBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FunctionCallDeriver extends DeriverBase {

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
      $container->get('config.factory')->get('drupalmoduleupgrader.functions')->get('definitions')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_definition) {
    $derivatives = [];

    foreach ($this->config as $key => $info) {
      // $key can either be the name of a single function, or an arbitrary string
      // identifying a group of functions to handle.
      if (empty($info['functions'])) {
        $info['functions'] = [$key];
      }

      foreach ($info['functions'] as $function) {
        $variables = ['@function' => $function . '()'];

        $derivative = array_merge($base_definition, $info);
        $derivative['function'] = $function;
        $derivative['message'] = $this->t($derivative['message'], $variables);
        $derivative['description'] = $this->t('Analyzes calls to @function.', $variables);
        foreach ($derivative['documentation'] as &$doc) {
          $doc['title'] = $this->t($doc['title'], $variables);
        }
        unset($derivative['functions']);

        $derivatives[$function] = $derivative;
      }
    }

    return $derivatives;
  }

}
