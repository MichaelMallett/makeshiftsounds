<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ObjectMethodCallNode;

/**
 * @Converter(
 *  id = "form_set_value",
 *  description = @Translation("Rewrites calls to form_set_value().")
 * )
 */
class FormSetValue extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    return ObjectMethodCallNode::create($arguments[2]->remove(), 'setValueForElement')
      ->appendArgument(clone $arguments[0])
      ->appendArgument(clone $arguments[1]);
  }

}
