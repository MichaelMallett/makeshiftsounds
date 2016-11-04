<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Drupal\drupalmoduleupgrader\Utility\StringTransformTrait;

/**
 * @Converter(
 *  id = "blocks",
 *  description = @Translation("Converts Drupal 7 blocks to plugins."),
 *  hook = {
 *    "hook_block_configure",
 *    "hook_block_info",
 *    "hook_block_save",
 *    "hook_block_view"
 *  },
 *  fixme = @Translation("hook_!hook is gone in Drupal 8.

It has been left here by the Drupal Module Upgrader so that you can move its
logic into the appropriate block plugins, which should be in the
src/Plugin/Block directory. Once all logic is moved into the plugins, delete
this hook."),
 *  documentation = {
 *    "https://www.drupal.org/node/1880620"
 *  }
 * )
 */
class Blocks extends ConverterBase {

  use StringTransformTrait;

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    try {
      $blocks = $this->executeHook($target, 'block_info');
    }
    catch (\LogicException $e) {
      $this->log->warning($e->getMessage(), [
        'target' => $target->id(),
        'hook' => $this->pluginDefinition['hook'],
      ]);
      return;
    }

    $indexer = $target->getIndexer('function');

    foreach ($blocks as $id => $info) {
      // Render the block plugin's shell.
      $render = [
        '#theme' => 'dmu_block',
        '#module' => $target->id(),
        '#class' => $this->toTitleCase(preg_replace('/[^a-zA-Z0-9_]+/', '_', $id)),
        '#block_id' => $id,
        '#block_label' => $info['info'],
        '#configurable' => $indexer->has('block_configure'),
      ];
      $this->writeClass($target, $this->parse($render));
    }

    // Slap a FIXME on hook_block_info(), and on other block hooks which
    // may or may not exist.
    $this->addFixMe($target, 'block_info');

    if ($indexer->has('hook_block_view')) {
      $this->addFixMe($target, 'block_view');
    }
    if ($indexer->has('hook_block_save')) {
      $this->addFixMe($target, 'block_save');
    }
    if ($indexer->has('hook_block_configure')) {
      $this->addFixMe($target, 'block_configure');
    }
  }

  /**
   * Slaps a translated FIXME notice above a block-related hook.
   *
   * @param TargetInterface $target
   *  The target module.
   * @param string $hook
   *  The hook to put the FIXME on. It's up to the calling code to ensure
   *  that the hook actually exists.
   */
  private function addFixMe(TargetInterface $target, $hook) {
    $variables = ['!hook' => $hook];

    $function = $target
      ->getIndexer('function')
      ->get('hook_' . $hook)
      ->setDocComment($this->buildFixMe(NULL, $variables, self::DOC_COMMENT));

    $target->save($function);
  }

}
