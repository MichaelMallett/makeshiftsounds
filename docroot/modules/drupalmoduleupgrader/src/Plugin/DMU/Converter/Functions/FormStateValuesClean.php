<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ObjectMethodCallNode;

/**
 * @Converter(
 *  id = "form_state_values_clean",
 *  description = @Translation("Rewrites calls to form_state_values_clean().")
 * )
 */
class FormStateValuesClean extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    return ObjectMethodCallNode::create(clone $call->getArgumentList()->getItem(0), 'cleanValues');
  }

}
