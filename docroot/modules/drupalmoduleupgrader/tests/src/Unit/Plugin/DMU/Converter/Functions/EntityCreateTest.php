<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\EntityCreate
 */
class EntityCreateTest extends FunctionCallModifierTestBase {

  public function testRewrite() {
    $function_call = Parser::parseExpression('entity_create("node", $node_values)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::entityManager()->getStorage("node")->create($node_values)', $rewritten->getText());
  }

}
