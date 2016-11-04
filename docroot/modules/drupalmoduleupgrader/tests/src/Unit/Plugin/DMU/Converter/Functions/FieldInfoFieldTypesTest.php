<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\FieldInfoFieldTypes
 */
class FieldInfoFieldTypesTest extends FunctionCallModifierTestBase {

  public function testRewriteNoArguments() {
    $function_call = Parser::parseExpression('field_info_field_types()');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::service(\'plugin.manager.field.field_type\')->getDefinitions()', $rewritten->getText());
  }

  public function testRewriteFieldType() {
    $function_call = Parser::parseExpression('field_info_field_types("text")');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::service(\'plugin.manager.field.field_type\')->getDefinition("text")', $rewritten->getText());
  }

}
