<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;
use Pharborist\Types\ArrayNode;

/**
 * @Converter(
 *  id = "module_invoke",
 *  description = @Translation("Rewrites calls to module_invoke().")
 * )
 */
class ModuleInvoke extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments()->toArray();

    $invoke = ClassMethodCallNode::create('\Drupal', 'moduleHandler')
      ->appendMethodCall('invoke')
      ->appendArgument(array_shift($arguments)->remove())
      ->appendArgument(array_shift($arguments)->remove());

    if ($arguments) {
      $invoke->appendArgument(ArrayNode::create($arguments));
    }

    return $invoke;
  }

}
