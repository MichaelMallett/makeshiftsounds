<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter;

use Drupal\drupalmoduleupgrader\ConverterBase;
use Drupal\drupalmoduleupgrader\TargetInterface;

/**
 * @Converter(
 *  id = "hook_watchdog",
 *  description = @Translation("Converts hook_watchdog() to an implementation of \\Psr\\Log\\LoggerInterface."),
 *  hook = "hook_watchdog"
 * )
 */
class HookWatchdog extends ConverterBase {

  /**
   * {@inheritdoc}
   */
  public function convert(TargetInterface $target) {
    $this->writeService($target, 'default_logger', [
      'class' => 'Drupal\\' . $target->id() . '\\Logger\\DefaultLogger',
      'tags' => [
        [ 'name' => 'logger' ],
      ],
    ]);

    $render = [
      '#theme' => 'dmu_logger',
      '#module' => $target->id(),
    ];
    $this->writeClass($target, $this->parse($render));
  }

}
