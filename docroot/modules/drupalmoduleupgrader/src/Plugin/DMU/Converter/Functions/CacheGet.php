<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;

/**
 * @Converter(
 *  id = "cache_get",
 *  description = @Translation("Rewrites calls to cache_get().")
 * )
 */
class CacheGet extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    $get = ClassMethodCallNode::create('\Drupal', 'cache');
    if (sizeof($arguments) == 2) {
      $get->appendArgument(clone $arguments[1]);
    }

    return $get->appendMethodCall('get')->appendArgument(clone $arguments[0]);
  }

}
