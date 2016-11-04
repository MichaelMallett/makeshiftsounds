<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\FormExecuteHandlers
 */
class FormExecuteHandlersTest extends FunctionCallModifierTestBase {

  public function testRewriteValidate() {
    $function_call = Parser::parseExpression('form_execute_handlers("validate", $form, $form_state)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::formBuilder()->executeValidateHandlers($form, $form_state)', $rewritten->getText());
  }

  public function testRewriteSubmit() {
    $function_call = Parser::parseExpression('form_execute_handlers("submit", $form, $form_state)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::formBuilder()->executeSubmitHandlers($form, $form_state)', $rewritten->getText());
  }

  public function testRewriteInvalidHandlerType() {
    $function_call = Parser::parseExpression('form_execute_handlers("blorfable", $form, $form_state)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertNull($rewritten);
  }

}
