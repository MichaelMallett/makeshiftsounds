<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\ThemeGetRegistry
 */
class ThemeGetRegistryTest extends FunctionCallModifierTestBase {

  public function testRewriteNoArgument() {
    $function_call = Parser::parseExpression('theme_get_registry()');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::service(\'theme.registry\')->get()', $rewritten->getText());
  }

  public function testRewriteArgument() {
    $function_call = Parser::parseExpression('theme_get_registry(FALSE)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::service(\'theme.registry\')->getRuntime()', $rewritten->getText());

    $function_call = Parser::parseExpression('theme_get_registry("foo")');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::service(\'theme.registry\')->get()', $rewritten->getText());
  }

}
