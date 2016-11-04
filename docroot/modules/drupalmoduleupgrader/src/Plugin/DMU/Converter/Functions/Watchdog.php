<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Constants\ConstantNode;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ClassMethodCallNode;
use Pharborist\Types\ArrayNode;

/**
 * @Converter(
 *  id = "watchdog",
 *  description = @Translation("Converts calls to watchdog() to \Drupal::logger().")
 * )
 */
class Watchdog extends FunctionCallModifier {

  protected static $severityConstants = [
    'WATCHDOG_EMERGENCY',
    'WATCHDOG_ALERT',
    'WATCHDOG_CRITICAL',
    'WATCHDOG_ERROR',
    'WATCHDOG_WARNING',
    'WATCHDOG_NOTICE',
    'WATCHDOG_INFO',
    'WATCHDOG_DEBUG',
  ];

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    // We'll call a specific method on the logger object, depending on the
    // severity passed in the original function call (if any). If there are
    // at least four arguments, a severity was passed. We check $arguments[3]
    // to ensure it's a valid severity constant, and if it's not, we default
    // to the notice() severity.
    //
    // @TODO Leave a FIXME for an invalid severity, since changing it to a
    // notice alters the intent of the original code.
    //
    if (sizeof($arguments) > 3 && $arguments[3] instanceof ConstantNode && in_array($arguments[3]->getConstantName()->getText(), static::$severityConstants)) {
      $method = strtolower(subStr($arguments[3], 9));
    }
    else {
      $method = 'notice';
    }

    // If there is a third argument, and it's an array, a context array
    // was passed.
    $context = (sizeof($arguments) > 2 && $arguments[2] instanceof ArrayNode) ? clone $arguments[2] : ArrayNode::create([]);

    return ClassMethodCallNode::create('\Drupal', 'logger')
      ->appendArgument(clone $arguments[0])
      ->appendMethodCall($method)
      ->appendArgument(clone $arguments[1])
      ->appendArgument($context);
  }

}
