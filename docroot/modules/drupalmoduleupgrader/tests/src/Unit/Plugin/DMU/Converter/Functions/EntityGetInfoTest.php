<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\EntityGetInfo
 */
class EntityGetInfoTest extends FunctionCallModifierTestBase {

  public function testRewriteNoArguments() {
    $function_call = Parser::parseExpression('entity_get_info()');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::entityManager()->getDefinitions()', $rewritten->getText());
  }

  public function testRewriteEntityType() {
    $function_call = Parser::parseExpression('entity_get_info("node")');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::entityManager()->getDefinition("node")', $rewritten->getText());
  }

}
