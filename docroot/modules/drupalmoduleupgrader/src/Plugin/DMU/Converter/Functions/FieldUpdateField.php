<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ObjectMethodCallNode;

/**
 * @Converter(
 *  id = "field_update_field",
 *  description = @Translation("Rewrites calls to field_update_field().")
 * )
 */
class FieldUpdateField extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    return ObjectMethodCallNode::create(clone $call->getArgumentList()->getItem(0), 'save');
  }

}
