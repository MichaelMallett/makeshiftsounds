<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;

/**
 * @Converter(
 *  id = "variable_del",
 *  description = @Translation("Replaces variable_del() calls with Configuration API.")
 * )
 */
class VariableDel extends VariableAPI {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    if ($this->tryRewrite($call, $target)) {
      return ClassMethodCallNode::create('\Drupal', 'config')
        ->appendArgument($target->id() . '.settings')
        ->appendMethodCall('clear')
        ->appendArgument(clone $call->getArguments()->get(0))
        ->appendMethodCall('save');
    }
  }

}
