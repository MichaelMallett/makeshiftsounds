<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;
use Pharborist\Types\ArrayNode;

/**
 * @Converter(
 *  id = "entity_load",
 *  description = @Translation("Rewrites calls to entity_load().")
 * )
 */
class EntityLoad extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    // If there were three arguments, the call is affecting the internal
    // entity cache. Unfortunately, it's pretty much impossible to reliably
    // determine whether or not they wanted to reset the cache, so let's just
    // leave a FIXME.
    if (sizeof($arguments) == 3) {
      $this->buildFixMe('To reset the entity cache, use EntityStorageInterface::resetCache().')->insertBefore($call);
    }

    $rewritten = ClassMethodCallNode::create('\Drupal', 'entityManager')
      ->appendMethodCall('getStorage')
      ->appendArgument(clone $arguments[0]);

    // If there's a third argument, conditions were passed. Not a recommended
    // practice, but certain modules might have done it anyway. If we detect
    // conditions, use loadByProperties().
    if (sizeof($arguments) > 2) {
      return $rewritten
        ->appendMethodCall('loadByProperties')
        ->appendArgument(clone $arguments[2]);
    }
    else {
      $rewritten->appendMethodCall('load');

      if (sizeof($arguments) > 1 && $arguments[1] instanceof ArrayNode) {
        $rewritten->appendArgument(clone $arguments[1]);
      }

      return $rewritten;
    }
  }

}
