<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Objects\ObjectMethodCallNode;
use Pharborist\Parser;
use Pharborist\Types\StringNode;

/**
 * @Converter(
 *  id = "field_view_field",
 *  description = @Translation("Rewrites calls to field_view_field().")
 * )
 */
class FieldViewField extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    $arguments = $call->getArguments();

    $property = $arguments[2] instanceof StringNode ? $arguments[2]->toValue() : clone $arguments[2];
    $rewritten = ObjectMethodCallNode::create(Parser::parseExpression($arguments[1] . '->' . $property), 'view');

    if (sizeof($arguments) >= 4) {
      $rewritten->appendArgument(clone $arguments[3]);
    }

    return $rewritten;
  }

}
