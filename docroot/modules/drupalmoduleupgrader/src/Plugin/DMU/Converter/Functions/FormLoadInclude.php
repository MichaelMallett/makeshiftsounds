<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ObjectMethodCallNode;

/**
 * @Converter(
 *  id = "form_load_include",
 *  description = @Translation("Rewrites calls to form_load_include().")
 * )
 */
class FormLoadInclude extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    $rewritten = ObjectMethodCallNode::create(clone $arguments[0], 'loadInclude')
      ->appendArgument(clone $arguments[2])
      ->appendArgument(clone $arguments[1]);

    if (sizeof($arguments) == 4) {
      $rewritten->appendArgument(clone $arguments[3]);
    }

    return $rewritten;
  }

}
