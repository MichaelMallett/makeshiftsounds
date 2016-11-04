<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ObjectMethodCallNode;

/**
 * @Converter(
 *  id = "_entity_operation",
 *  deriver = "Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\EntityOperationDeriver"
 * )
 */
class EntityOperation extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();
    $object = (strPos($call->getName()->getText(), 'entity_') === 0 ? $arguments[1] : $arguments[0]);

    return ObjectMethodCallNode::create(clone $object, $this->pluginDefinition['method']);
  }

}
