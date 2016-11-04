<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * @Converter(
 *  id = "hook_exit",
 *  description = @Translation("Converts Drupal 7's hook_exit() to an EventSubscriber."),
 *  hook = "hook_exit"
 * )
 */
class HookExit extends ConverterBase {

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    $this->writeService($target, 'exit_subscriber', [
      'class' => 'Drupal\\' . $target->id() . '\\EventSubscriber\\ExitSubscriber',
      'tags' => [
        [ 'name' => 'event_subscriber' ],
      ],
    ]);

    $render = [
      '#theme' => 'dmu_event_subscriber',
      '#module' => $target->id(),
      '#class' => 'ExitSubscriber',
      '#event' => 'KernelEvents::TERMINATE',
    ];
    $subscriber = $this->parse($render);
    $target
      ->getIndexer('function')
      ->get('hook_exit')
      ->cloneAsMethodOf($subscriber)
      ->setName('onEvent');
    $this->writeClass($target, $subscriber);
  }

}
