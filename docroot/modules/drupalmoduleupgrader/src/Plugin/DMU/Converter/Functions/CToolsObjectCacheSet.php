<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;

/**
 * @Converter(
 *  id = "ctools_object_cache_set",
 *  description = @Translation("Rewrites calls to ctools_object_cache_set().")
 * )
 */
class CToolsObjectCacheSet extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments()->toArray();
    array_shift($arguments);

    if (sizeof($arguments) == 3) {
      array_pop($arguments);
    }

    return ClassMethodCallNode::create('\Drupal', 'service')
      ->appendArgument('user.tempstore')
      ->appendMethodCall('set')
      ->appendArgument($arguments[0])
      ->appendArgument($arguments[1]);
  }

}
