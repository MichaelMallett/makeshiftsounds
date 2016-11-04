<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\CToolsGetPlugins;
use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\CToolsGetPlugins
 */
class CToolsGetPluginsTest extends FunctionCallModifierTestBase {

  public function setUp() {
    parent::setUp();
    $this->plugin = CToolsGetPlugins::create($this->container, [], 'ctools_get_plugins', []);
  }

  public function testCanRewriteValidFunctionCall() {
    $function_call = Parser::parseExpression('ctools_get_plugins("foo", "foobaz")');
    $this->assertTrue($this->plugin->canRewrite($function_call, $this->target));
  }

  public function testCanRewriteInvalidFunctionCall() {
    $function_call = Parser::parseExpression('ctools_get_plugins($module_name, "foobaz")');
    $this->assertFalse($this->plugin->canRewrite($function_call, $this->target));
  }

  public function testRewriteValidFunctionCall() {
    $function_call = Parser::parseExpression('ctools_get_plugins("foo", "foobaz")');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::service(\'plugin.manager.foo.foobaz\')->getDefinitions()', $rewritten->getText());
  }

  public function testRewriteInvalidFunctionCall() {
    $function_call = Parser::parseExpression('ctools_get_plugins($module_name, "foobaz")');
    $this->assertNull($this->plugin->rewrite($function_call, $this->target));
  }

}
