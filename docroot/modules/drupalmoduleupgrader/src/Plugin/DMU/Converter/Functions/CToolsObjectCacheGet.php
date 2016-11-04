<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;

/**
 * @Converter(
 *  id = "ctools_object_cache_get",
 *  description = @Translation("Rewrites calls to ctools_object_cache_get().")
 * )
 */
class CToolsObjectCacheGet extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    return ClassMethodCallNode::create('\Drupal', 'service')
      ->appendArgument('user.tempstore')
      ->appendMethodCall('get')
      ->appendArgument(clone $call->getArgumentList()->getItem(1));
  }

}
