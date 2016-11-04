<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ObjectMethodCallNode;

/**
 * @Converter(
 *  id = "user_save",
 *  description = @Translation("Rewrites calls to user_save()."),
 *  fixme = @Translation("user_save() is now a method of the user entity.")
 * )
 */
class UserSave extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    if (sizeof($arguments) == 1) {
      return ObjectMethodCallNode::create(clone $arguments[0], 'save');
    }
  }

}
