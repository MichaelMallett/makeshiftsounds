<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Drupal\drupalmoduleupgrader\Utility\StringTransformTrait;

/**
 * @Converter(
 *  id = "hook_field_formatter_info",
 *  description = @Translation("Creates formatter class templates from hook_field_formatter_info()."),
 *  hook = "hook_field_formatter_info"
 * )
 */
class HookFieldFormatterInfo extends ConverterBase {

  use StringTransformTrait;

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    try {
      $formatters = $this->executeHook($target, $this->pluginDefinition['hook']);
    }
    catch (\LogicException $e) {
      $this->log->warning($e->getMessage(), [
        'target' => $target->id(),
        'hook' => $this->pluginDefinition['hook'],
      ]);
      return;
    }

    foreach ($formatters as $id => $formatter) {
      $render = [
        '#module' => $target->id(),
        '#class' => $this->toTitleCase($id),
        '#theme' => 'dmu_formatter',
        '#info' => [
          'id' => $id,
          'label' => $formatter['label'],
          'description' => $formatter['description'] ?: NULL,
          'field_types' => $formatter['field types'],
        ],
      ];
      $this->writeClass($target, $this->parse($render));
    }
  }

}
