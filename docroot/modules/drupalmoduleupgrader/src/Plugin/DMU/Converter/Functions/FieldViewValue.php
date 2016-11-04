<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ObjectMethodCallNode;

/**
 * @Converter(
 *  id = "field_view_value",
 *  description = @Translation("Rewrites calls to field_view_value().")
 * )
 */
class FieldViewValue extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    $rewritten = ObjectMethodCallNode::create(clone $arguments[3], 'view');
    if (sizeof($arguments) >= 5) {
      $rewritten->appendArgument(clone $arguments[4]);
    }

    return $rewritten;
  }

}
