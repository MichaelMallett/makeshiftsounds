<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Drupal\drupalmoduleupgrader\Utility\StringTransformTrait;

/**
 * @Converter(
 *  id = "hook_field_widget_info",
 *  description = @Translation("Creates formatter class templates from hook_field_widget_info()."),
 *  hook = "hook_field_widget_info"
 * )
 */
class HookFieldWidgetInfo extends ConverterBase {

  use StringTransformTrait;

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    try {
      $widgets = $this->executeHook($target, $this->pluginDefinition['hook']);
    }
    catch (\LogicException $e) {
      $this->logger->warning($e->getMessage(), [
        'target' => $target->id(),
        'hook' => $this->pluginDefinition['hook'],
      ]);
      return;
    }

    foreach ($widgets as $id => $widget) {
      $render = [
        '#module' => $target->id(),
        '#class' => $this->toTitleCase($id),
        '#theme' => 'dmu_widget',
        '#info' => [
          'id' => $id,
          'label' => $widget['label'],
          'description' => $widget['description'] ?: NULL,
          'field_types' => $widget['field types'],
        ],
      ];
      $this->writeClass($target, $this->parse($render));
    }
  }

}
