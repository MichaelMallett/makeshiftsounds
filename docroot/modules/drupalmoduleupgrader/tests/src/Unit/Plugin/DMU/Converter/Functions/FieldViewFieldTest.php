<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\FieldViewField
 */
class FieldViewFieldTest extends FunctionCallModifierTestBase {

  public function testRewriteViewMode() {
    $function_call = Parser::parseExpression('field_view_field("node", $node, "field_foo", $view_mode, $langcode)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$node->field_foo->view($view_mode)', $rewritten->getText());
  }

  public function testRewriteDisplayOptions() {
    $function_call = Parser::parseExpression('field_view_field("node", $node, "field_foo", array("type" => "some_formatter"), $langcode)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$node->field_foo->view(array("type" => "some_formatter"))', $rewritten->getText());
  }

}
