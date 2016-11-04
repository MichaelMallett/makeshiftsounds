<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;
use Pharborist\Types\FalseNode;
use Pharborist\Types\StringNode;

/**
 * @Converter(
 *  id = "theme_get_registry",
 *  description = @Translation("Rewrites calls to theme_get_registry().")
 * )
 */
class ThemeGetRegistry extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments()->toArray();

    return ClassMethodCallNode::create('\Drupal', 'service')
      ->appendArgument(StringNode::fromValue('theme.registry'))
      ->appendMethodCall(($arguments && $arguments[0] instanceof FalseNode) ? 'getRuntime' : 'get');
  }

}
