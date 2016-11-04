<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;

/**
 * @Converter(
 *  id = "variable_set",
 *  description = @Translation("Replaces variable_set() calls with Configuration API.")
 * )
 */
class VariableSet extends VariableAPI {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    if ($this->tryRewrite($call, $target)) {
      $arguments = $call->getArguments();

      return ClassMethodCallNode::create('\Drupal', 'configFactory')
        ->appendMethodCall('getEditable')
        ->appendArgument($target->id() . '.settings')
        ->appendMethodCall('set')
        ->appendArgument(clone $arguments[0])
        ->appendArgument(clone $arguments[1])
        ->appendMethodCall('save');
    }
  }

}
