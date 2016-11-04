<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\FieldInfoFormatterTypes
 */
class FieldInfoFormatterTypesTest extends FunctionCallModifierTestBase {

  public function testRewriteNoArguments() {
    $function_call = Parser::parseExpression('field_info_formatter_types()');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::service(\'plugin.manager.field.formatter\')->getDefinitions()', $rewritten->getText());
  }

  public function testRewriteFieldType() {
    $function_call = Parser::parseExpression('field_info_formatter_types("text_default")');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::service(\'plugin.manager.field.formatter\')->getDefinition("text_default")', $rewritten->getText());
  }

}
