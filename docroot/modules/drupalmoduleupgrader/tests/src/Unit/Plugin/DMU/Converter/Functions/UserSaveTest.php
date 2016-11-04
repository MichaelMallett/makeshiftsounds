<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\UserSave
 */
class UserSaveTest extends FunctionCallModifierTestBase {

  public function testRewriteWithoutEditArray() {
    $function_call = Parser::parseExpression('user_save($account)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$account->save()', $rewritten->getText());
  }

  public function testRewriteWithEditArray() {
    $function_call = Parser::parseExpression('user_save($account, array())');
    $this->assertNull($this->plugin->rewrite($function_call, $this->target));
  }

  public function testRewriteWithEditArrayAndCategory() {
    $function_call = Parser::parseExpression('user_save($account, array(), "Foo")');
    $this->assertNull($this->plugin->rewrite($function_call, $this->target));
  }

}
