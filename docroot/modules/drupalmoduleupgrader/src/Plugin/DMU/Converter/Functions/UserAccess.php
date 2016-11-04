<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;
use Pharborist\Objects\ObjectMethodCallNode;
use Pharborist\Variables\VariableNode;

/**
 * @Converter(
 *  id = "user_access",
 *  description = @Translation("Rewrites calls to user_access().")
 * )
 */
class UserAccess extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    if (isset($arguments[1]) && $arguments[1] instanceof VariableNode) {
      $rewritten = ObjectMethodCallNode::create(clone $arguments[1], 'hasPermission');
    }
    else {
      $rewritten = ClassMethodCallNode::create('\Drupal', 'currentUser')->appendMethodCall('hasPermission');
    }

    return $rewritten->appendArgument(clone $arguments[0]);
  }

}
