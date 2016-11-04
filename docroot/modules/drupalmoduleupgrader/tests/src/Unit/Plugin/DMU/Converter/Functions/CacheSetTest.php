<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\CacheSet
 */
class CacheSetTest extends FunctionCallModifierTestBase {

  public function testRewriteDefaultBin() {
    $function_call = Parser::parseExpression('cache_set("foo", array())');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::cache()->set("foo", array())', $rewritten->getText());
  }

  public function testRewriteSpecificBinNoExpiration() {
    $function_call = Parser::parseExpression('cache_set("foo", array(), "baz")');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::cache("baz")->set("foo", array())', $rewritten->getText());
  }

  public function testRewriteSpecificBinWithExpiration() {
    $function_call = Parser::parseExpression('cache_set("foo", array(), "bar", 67890)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::cache("bar")->set("foo", array(), 67890)', $rewritten->getText());
  }

}
