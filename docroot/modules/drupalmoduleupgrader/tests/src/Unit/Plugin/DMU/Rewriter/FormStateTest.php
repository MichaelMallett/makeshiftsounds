<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Rewriter;

use Drupal\Tests\drupalmoduleupgrader\Unit\TestBase;
use Pharborist\Parser;

/**
 * @group DMU.Rewriter
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Rewriter\FormState
 */
class FormStateTest extends TestBase {

  /**
   * @var \Drupal\drupalmoduleupgrader\RewriterInterface
   */
  protected $plugin;

  public function setUp() {
    parent::setUp();

    $definition = [
      'properties' => [
        'input' => [
          'get' => 'getUserInput',
          'set' => 'setUserInput',
        ],
      ],
    ];
    $this->plugin = $this->getPlugin([], $definition);
  }

  public function testRewriteValuesAsGetter() {
    $expr = Parser::parseExpression('$form_state["values"]');
    $rewritten = $this->plugin->rewriteAsGetter($expr, 'values');
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$form_state->getValues()', $rewritten->getText());

    $expr = Parser::parseExpression('$form_state["values"]["foo"]');
    $rewritten = $this->plugin->rewriteAsGetter($expr, 'values');
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$form_state->getValue(["foo"])', $rewritten->getText());

    $expr = Parser::parseExpression('$form_state["values"]["foo"][0]');
    $rewritten = $this->plugin->rewriteAsGetter($expr, 'values');
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$form_state->getValue(["foo", 0])', $rewritten->getText());
  }

  public function testRewriteKnownPropertyAsGetter() {
    $expr = Parser::parseExpression('$form_state["input"]');
    $rewritten = $this->plugin->rewriteAsGetter($expr, 'input');
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$form_state->getUserInput()', $rewritten->getText());
  }

  public function testRewriteArbitraryKeyAsGetter() {
    $expr = Parser::parseExpression('$form_state["foo"]["baz"]');
    $rewritten = $this->plugin->rewriteAsGetter($expr, 'foo');
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$form_state->get(["foo", "baz"])', $rewritten->getText());
  }

  public function testRewriteValuesAsSetter() {
    /** @var \Pharborist\Operators\AssignNode $expr */
    $expr = Parser::parseExpression('$form_state["values"]["foo"] = "baz"');
    $rewritten = $this->plugin->rewriteAsSetter($expr->getLeftOperand(), 'values', $expr);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$form_state->setValue(["foo"], "baz")', $rewritten->getText());

    $expr = Parser::parseExpression('$form_state["values"]["foo"][1] = "bar"');
    $rewritten = $this->plugin->rewriteAsSetter($expr->getLeftOperand(), 'values', $expr);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$form_state->setValue(["foo", 1], "bar")', $rewritten->getText());
  }

  public function testRewriteKnownPropertyAsSetter() {
    /** @var \Pharborist\Operators\AssignNode $expr */
    $expr = Parser::parseExpression('$form_state["input"] = array()');
    $rewritten = $this->plugin->rewriteAsSetter($expr->getLeftOperand(), 'input', $expr);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$form_state->setUserInput(array())', $rewritten->getText());
  }

  public function testRewriteArbitraryKeyAsSetter() {
    /** @var \Pharborist\Operators\AssignNode $expr */
    $expr = Parser::parseExpression('$form_state["foo"]["baz"] = "bar"');
    $rewritten = $this->plugin->rewriteAsSetter($expr->getLeftOperand(), 'foo', $expr);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('$form_state->set(["foo", "baz"], "bar")', $rewritten->getText());
  }

}
