<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;
use Pharborist\Types\ArrayNode;
use Pharborist\Types\StringNode;

/**
 * @Converter(
 *  id = "drupal_write_record",
 *  description = @Translation("Rewrites calls to drupal_write_record().")
 * )
 */
class DrupalWriteRecord extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $rewritten = ClassMethodCallNode::create('\Drupal', 'database');

    $arguments = $call->getArguments();
    if (sizeof($arguments) == 3) {
      $key = $arguments[2] instanceof StringNode ? ArrayNode::create([ clone $arguments[2] ]) : clone $arguments[2];

      return $rewritten
        ->appendMethodCall('merge')
        ->appendArgument(clone $arguments[0])
        ->appendMethodCall('fields')
        ->appendArgument(clone $arguments[1])
        ->appendMethodCall('key')
        ->appendArgument($key)
        ->appendMethodCall('execute');
    }
    else {
      return $rewritten
        ->appendMethodCall('insert')
        ->appendArgument(clone $arguments[0])
        ->appendMethodCall('fields')
        ->appendArgument(clone $arguments[1])
        ->appendMethodCall('execute');
    }
  }

}
