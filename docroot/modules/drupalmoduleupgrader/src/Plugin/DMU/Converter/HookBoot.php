<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\ParameterNode;

/**
 * @Converter(
 *  id = "hook_boot",
 *  description = @Translation("Converts Drupal 7's hook_boot() to an EventSubscriber."),
 *  hook = "hook_boot"
 * )
 */
class HookBoot extends ConverterBase {

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    $this->writeService($target, 'boot_subscriber', [
      'class' => 'Drupal\\' . $target->id() . '\\EventSubscriber\\BootSubscriber',
      'tags' => [
        [ 'name' => 'event_subscriber' ],
      ],
    ]);

    $render = [
      '#theme' => 'dmu_event_subscriber',
      '#module' => $target->id(),
      '#class' => 'BootSubscriber',
      '#event' => 'KernelEvents::REQUEST',
    ];
    $subscriber = $this->parse($render);

    $target
      ->getIndexer('function')
      ->get('hook_boot')
      ->cloneAsMethodOf($subscriber)
      ->setName('onEvent')
      ->appendParameter(ParameterNode::create('event')
        ->setTypeHint('\Symfony\Component\HttpKernel\Event\GetResponseEvent')
      );

    $this->writeClass($target, $subscriber);
  }

}
