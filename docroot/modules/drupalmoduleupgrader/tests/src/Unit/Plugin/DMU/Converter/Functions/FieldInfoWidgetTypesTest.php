<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\FieldInfoWidgetTypes
 */
class FieldInfoWidgetTypesTest extends FunctionCallModifierTestBase {

  public function testRewriteNoArguments() {
    $function_call = Parser::parseExpression('field_info_widget_types()');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::service(\'plugin.manager.field.widget\')->getDefinitions()', $rewritten->getText());
  }

  public function testRewriteFieldType() {
    $function_call = Parser::parseExpression('field_info_widget_types("text_textfield")');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::service(\'plugin.manager.field.widget\')->getDefinition("text_textfield")', $rewritten->getText());
  }

}
