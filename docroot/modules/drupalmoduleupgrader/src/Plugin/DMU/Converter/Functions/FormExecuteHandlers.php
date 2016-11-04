<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;
use Pharborist\Types\StringNode;

/**
 * @Converter(
 *  id = "form_execute_handlers",
 *  description = @Translation("Rewrites calls to form_execute_handlers()."),
 *  fixme = @Translation("form_execute_handlers() has been split into the executeValidateHandlers() and executeSubmitHandlers() methods of the form builder service.")
 * )
 */
class FormExecuteHandlers extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    if ($arguments[0] instanceof StringNode) {
      $handler_type = $arguments[0]->toValue();

      if ($handler_type == 'validate' || $handler_type == 'submit') {
        return ClassMethodCallNode::create('\Drupal', 'formBuilder')
          ->appendMethodCall('execute' . ucFirst($handler_type) . 'Handlers')
          ->appendArgument(clone $arguments[1])
          ->appendArgument(clone $arguments[2]);
      }
    }
  }

}
