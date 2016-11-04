<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * @Converter(
 *  id = "hook_init",
 *  description = @Translation("Converts Drupal 7's hook_init() to an EventSubscriber."),
 *  hook = "hook_init"
 * )
 */
class HookInit extends ConverterBase {

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    $this->writeService($target, 'init_subscriber', [
      'class' => 'Drupal\\' . $target->id() . '\\EventSubscriber\\InitSubscriber',
      'tags' => [
        [ 'name' => 'event_subscriber' ],
      ],
    ]);

    $render = [
      '#theme' => 'dmu_event_subscriber',
      '#module' => $target->id(),
      '#class' => 'InitSubscriber',
      '#event' => 'KernelEvents::REQUEST',
    ];
    $subscriber = $this->parse($render);
    $target
      ->getIndexer('function')
      ->get('hook_init')
      ->cloneAsMethodOf($subscriber)
      ->setName('onEvent');
    $this->writeClass($target, $subscriber);
  }

}
