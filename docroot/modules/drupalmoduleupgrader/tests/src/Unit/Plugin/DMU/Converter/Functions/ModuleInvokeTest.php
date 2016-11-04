<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\ModuleInvoke
 */
class ModuleInvokeTest extends FunctionCallModifierTestBase {

  public function testRewriteNoArguments() {
    $function_call = Parser::parseExpression('module_invoke_all("foo", "menu")');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::moduleHandler()->invoke("foo", "menu")', $rewritten->getText());
  }

  public function testRewriteArguments() {
    $function_call = Parser::parseExpression('module_invoke_all("foo", "menu_alter", $menu)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::moduleHandler()->invoke("foo", "menu_alter", [$menu])', $rewritten->getText());
  }

}
