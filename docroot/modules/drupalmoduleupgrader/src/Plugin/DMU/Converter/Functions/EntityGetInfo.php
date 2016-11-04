<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;

/**
 * @Converter(
 *  id = "entity_get_info",
 *  description = @Translation("Rewrites calls to entity_get_info().")
 * )
 */
class EntityGetInfo extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $manager = ClassMethodCallNode::create('\Drupal', 'entityManager');

    $arguments = $call->getArguments();
    if ($arguments->isEmpty()) {
      return $manager->appendMethodCall('getDefinitions');
    }
    elseif (sizeof($arguments) == 1) {
      return $manager
        ->appendMethodCall('getDefinition')
        ->appendArgument(clone $arguments[0]);
    }
  }

}
