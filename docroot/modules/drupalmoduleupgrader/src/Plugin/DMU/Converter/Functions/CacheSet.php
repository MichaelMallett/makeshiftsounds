<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;

/**
 * @Converter(
 *  id = "cache_set",
 *  description = @Translation("Rewrites calls to cache_set().")
 * )
 */
class CacheSet extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    $cache = ClassMethodCallNode::create('\Drupal', 'cache');
    if (sizeof($arguments) > 2) {
      $cache->appendArgument(clone $arguments[2]);
    }

    $set = $cache->appendMethodCall('set')
      ->appendArgument(clone $arguments[0])
      ->appendArgument(clone $arguments[1]);

    // Include the expiration time, if given.
    if (sizeof($arguments) == 4) {
      $set->appendArgument(clone $arguments[3]);
    }

    return $set;
  }

}
