<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\Watchdog
 */
class WatchdogTest extends FunctionCallModifierTestBase {

  public function testRewriteNoVariablesDefaultSeverity() {
    $function_call = Parser::parseExpression('watchdog("foo", "Hi!")');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::logger("foo")->notice("Hi!", [])', $rewritten->getText());
  }

  public function testRewriteVariablesDefaultSeverity() {
    $function_call = Parser::parseExpression('watchdog("foo", "Hej", array("baz"))');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::logger("foo")->notice("Hej", array("baz"))', $rewritten->getText());
  }

  public function testRewriteNoVariablesSeverity() {
    $function_call = Parser::parseExpression('watchdog("foo", "Harrr", NULL, WATCHDOG_WARNING)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::logger("foo")->warning("Harrr", [])', $rewritten->getText());
  }

  public function testRewriteVariablesSeverity() {
    $function_call = Parser::parseExpression('watchdog("foo", "Hurrr", array("baz"), WATCHDOG_ERROR)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::logger("foo")->error("Hurrr", array("baz"))', $rewritten->getText());
  }

  public function testRewriteNoVariablesDynamicSeverity() {
    $function_call = Parser::parseExpression('watchdog("foo", "Barrr", NULL, get_severity())');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::logger("foo")->notice("Barrr", [])', $rewritten->getText());
  }

  public function testRewriteVariablesTernarySeverity() {
    $function_call = Parser::parseExpression('watchdog("foo", "Yarrr", array(0), $bipolar ? WATCHDOG_NOTICE : WATCHDOG_CRITICAL)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::logger("foo")->notice("Yarrr", array(0))', $rewritten->getText());
  }

  public function testRewriteNoVariablesUnknownSeverity() {
    $function_call = Parser::parseExpression('watchdog("foo", "Ba-zing!", NULL, WATCHDOG_FOO)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::logger("foo")->notice("Ba-zing!", [])', $rewritten->getText());
  }

}
