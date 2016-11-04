<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;

/**
 * @Converter(
 *  id = "_disable",
 *  deriver = "Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\DisableDeriver"
 * )
 */
final class Disable extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    return NULL;
  }

}
