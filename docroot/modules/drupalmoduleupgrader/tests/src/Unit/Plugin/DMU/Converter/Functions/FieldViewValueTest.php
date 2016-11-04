<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\FieldViewValue
 */
class FieldViewValueTest extends FunctionCallModifierTestBase {

  public function testRewriteViewMode() {
    $function_call = Parser::parseExpression('field_view_value("node", $node, "field_foo", $item, $view_mode, $langcode)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$item->view($view_mode)', $rewritten->getText());
  }

  public function testRewriteDisplayOptions() {
    $function_call = Parser::parseExpression('field_view_value("node", $node, "field_foo", $item, array("type" => "some_formatter"), $langcode)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$item->view(array("type" => "some_formatter"))', $rewritten->getText());
  }

}
