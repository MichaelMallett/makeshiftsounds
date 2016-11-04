<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\DrupalGetTitle
 */
class DrupalGetTitleTest extends FunctionCallModifierTestBase {

  public function setUp() {
    parent::setUp();
    $this->plugin = $this->getPlugin();
  }

  public function testRewrite() {
    $function_call = Parser::parseExpression('drupal_get_title()');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::service(\'title_resolver\')->getTitle(\Drupal::request(), \Drupal::routeMatch()->getRouteObject())', $rewritten->getText());
  }

}
