<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;

/**
 * @Converter(
 *  id = "drupal_get_title",
 *  description = @Translation("Rewrites calls to drupal_get_title().")
 * )
 */
class DrupalGetTitle extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    return ClassMethodCallNode::create('\Drupal', 'service')
      ->appendArgument('title_resolver')
      ->appendMethodCall('getTitle')
      ->appendArgument(ClassMethodCallNode::create('\Drupal', 'request'))
      ->appendArgument(ClassMethodCallNode::create('\Drupal', 'routeMatch')->appendMethodCall('getRouteObject'));
  }

}
