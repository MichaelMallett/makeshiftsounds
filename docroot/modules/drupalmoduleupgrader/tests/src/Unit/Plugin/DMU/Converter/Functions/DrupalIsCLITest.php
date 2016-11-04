<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\DrupalIsCLI
 */
class DrupalIsCLITest extends FunctionCallModifierTestBase {

  public function testRewrite() {
    $function_call = Parser::parseExpression('drupal_is_cli()');
    $rewritten = $this->getPlugin()->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\ExpressionNode', $rewritten);
    $this->assertEquals('(PHP_SAPI === "cli")', $rewritten->getText());
  }

}
