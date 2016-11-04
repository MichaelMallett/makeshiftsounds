<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\CToolsObjectCacheSet
 */
class CToolsObjectCacheSetTest extends FunctionCallModifierTestBase {

  public function testRewriteNoSessionID() {
    $function_call = Parser::parseExpression('ctools_object_cache_set("foo", "baz", array())');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::service(\'user.tempstore\')->set("baz", array())', $rewritten->getText());
  }

  public function testRewriteSessionID() {
    $function_call = Parser::parseExpression('ctools_object_cache_set("foo", "baz", array(), "SESSION_ID")');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::service(\'user.tempstore\')->set("baz", array())', $rewritten->getText());
  }

}
