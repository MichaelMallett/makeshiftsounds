<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\DrupalMapAssoc
 */
class DrupalMapAssocTest extends FunctionCallModifierTestBase {

  public function testRewrite() {
    $function_call = Parser::parseExpression('drupal_map_assoc(array(0, 1, 2, 3))');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Functions\FunctionCallNode', $rewritten);
    $this->assertSame($rewritten, $function_call);
    $this->assertEquals('array_combine(array(0, 1, 2, 3), array(0, 1, 2, 3))', $rewritten->getText());
  }

}
