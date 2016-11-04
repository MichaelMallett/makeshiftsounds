<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;

/**
 * @Converter(
 *  id = "node_load",
 *  description = @Translation("Rewrites calls to node_load()."),
 *  fixme = @Translation("node_load() is now EntityStorageInterface::load().")
 * )
 */
class NodeLoad extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    // If there were three arguments, the call is affecting the internal
    // node_load() cache. Unfortunately, it's pretty much impossible to
    // reliably determine whether or not they wanted to reset the cache,
    // so let's just leave a FIXME.
    if (sizeof($arguments) == 3) {
      $this->buildFixMe('To reset the node cache, use EntityStorageInterface::resetCache().')->insertBefore($call);
    }

    $rewritten = ClassMethodCallNode::create('\Drupal', 'entityManager')
      ->appendMethodCall('getStorage')
      ->appendArgument('node');

    // If there's more than one argument, a revision ID was passed, which
    // means we call loadRevision($nid). Otherwise, call load($nid).
    if (sizeof($arguments) > 1) {
      return $rewritten
        ->appendMethodCall('loadRevision')
        ->appendArgument(clone $arguments[1]);
    }
    else {
      return $rewritten
        ->appendMethodCall('load')
        ->appendArgument(clone $arguments[0]);
    }
  }

}
