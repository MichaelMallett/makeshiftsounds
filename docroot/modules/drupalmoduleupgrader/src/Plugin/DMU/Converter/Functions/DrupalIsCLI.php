<?php

namespace Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\TargetInterface;
use Pharborist\Functions\FunctionCallNode;
use Pharborist\Parser;

/**
 * @Converter(
 *  id = "drupal_is_cli",
 *  description = @Translation("Rewrites calls to drupal_is_cli().")
 * )
 */
class DrupalIsCLI extends FunctionCallModifier {

  /**
   * {@inheritdoc}
   */
  public function rewrite(FunctionCallNode $call, TargetInterface $target) {
    return Parser::parseExpression('(PHP_SAPI === "cli")');
  }

}
