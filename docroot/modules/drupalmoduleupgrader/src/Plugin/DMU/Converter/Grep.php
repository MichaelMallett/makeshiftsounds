<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Parser;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Converter(
 *  id = "grep",
 *  description = @Translation("Searches for and replaces commonly-used code that has changed in Drupal 8.")
 * )
 */
class Grep extends ConverterBase {

  /**
   * @var string[]
   */
  private $targets = [];

  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $translator, LoggerInterface $log) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $translator, $log);

    foreach ($configuration['globals'] as $variable => $replacement) {
      $this->targets['global $' . $variable . ';'] = '$' . $variable . ' = ' . $replacement . ';';
      $this->targets['$GLOBALS[\'' . $variable . '\']'] = $replacement;
      $this->targets['$GLOBALS["' . $variable . '"]'] = $replacement;
    }
    foreach ($configuration['constants'] as $constant => $replacement) {
      $this->targets[$constant] = $replacement;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('config.factory')->get('drupalmoduleupgrader.grep')->get('definitions'),
      $plugin_id,
      $plugin_definition,
      $container->get('string_translation'),
      $container->get('logger.factory')->get('drupalmoduleupgrader')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    foreach ($this->configuration['function_calls'] as $function => $replace_with) {
      $function_calls = $target->getIndexer('function_call')->get($function);
      foreach ($function_calls as $function_call) {
        $rewritten = str_ireplace($function, $replace_with, $function_call->getText());
        $node = Parser::parseExpression($rewritten);
        $function_call->replaceWith($node);
        $target->save($node);
      }
    }

    // Flush other open syntax trees to ensure that other plugins don't clobber
    // our changes later.
    $target->flush();

    foreach ($target->getFinder() as $file) {
      // Load in the entire contents of the module. This is criminally inefficient
      // and wasteful of memory and should eventually be refactored into something
      // a little more...I dunno, sustainable.
      /** @var \Symfony\Component\Finder\SplFileInfo $file */
      $search = array_keys($this->targets);
      $replace = array_values($this->targets);
      file_put_contents($file->getPathname(), str_replace($search, $replace, $file->getContents()));
    }
  }

}
