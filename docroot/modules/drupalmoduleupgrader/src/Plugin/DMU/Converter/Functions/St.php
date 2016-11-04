<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;

/**
 * @Converter(
 *  id = "st",
 *  description = @Translation("Rewrites calls to st().")
 * )
 */
class St extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    return $call->setName('t');
  }

}
