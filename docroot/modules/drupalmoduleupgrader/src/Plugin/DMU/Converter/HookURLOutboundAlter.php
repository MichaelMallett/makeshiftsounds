<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * @Converter(
 *  id = "hook_url_outbound_alter",
 *  description = @Translation("Converts hook_url_outbound_alter() to a service."),
 *  hook = "hook_url_outbound_alter"
 * )
 */
class HookURLOutboundAlter extends ConverterBase {

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    $this->writeService($target, 'outbound_path_processor', [
      'class' => 'Drupal\\' . $target->id() . '\\OutboundPathProcessor',
      'tags' => [
        [ 'name' => 'path_processor_outbound' ],
      ],
    ]);

    $render = [
      '#theme' => 'dmu_outbound_path_processor',
      '#module' => $target->id(),
    ];
    $processor = $this->parse($render);
    $target
      ->getIndexer('function')
      ->get('hook_url_outbound_alter')
      ->cloneAsMethodOf($processor)
      ->setName('processOutbound');
    $this->writeClass($target, $processor);
  }

}
