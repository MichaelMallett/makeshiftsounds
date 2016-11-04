<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;

/**
 * @Converter(
 *  id = "user_load",
 *  description = @Translation("Rewrites calls to user_load()."),
 *  fixme = @Translation("user_load() is now EntityStorageInterface::load().")
 * )
 */
class UserLoad extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    // If there were three arguments, the call is affecting the internal
    // user_load() cache. Unfortunately, it's pretty much impossible to
    // reliably determine whether or not they wanted to reset the cache,
    // so let's just leave a FIXME.
    if (sizeof($arguments) == 2) {
      $this->buildFixMe('To reset the user cache, use EntityStorageInterface::resetCache().')->insertBefore($call);
    }

    return ClassMethodCallNode::create('\Drupal', 'entityManager')
      ->appendMethodCall('getStorage')
      ->appendArgument('user')
      ->appendMethodCall('load')
      ->appendArgument(clone $arguments[0]);
  }

}
