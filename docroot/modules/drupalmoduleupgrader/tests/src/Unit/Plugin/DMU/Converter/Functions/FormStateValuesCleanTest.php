<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\FormStateValuesClean
 */
class FormStateValuesCleanTest extends FunctionCallModifierTestBase {

  public function testRewrite() {
    $function_call = Parser::parseExpression('form_state_values_clean($form_state)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$form_state->cleanValues()', $rewritten->getText());
  }

}
