<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\St
 */
class StTest extends FunctionCallModifierTestBase {

  public function testRewrite() {
    $function_call = Parser::parseExpression('st("I translate thee!")');
    /** @var \Pharborist\Functions\FunctionCallNode $rewritten */
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertSame($function_call, $rewritten);
    $this->assertEquals('t("I translate thee!")', $rewritten->getText());
  }

}
