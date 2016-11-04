<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Filter;
use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\VariableGet
 */
class VariableGetTest extends FunctionCallModifierTestBase {

  public function testNonStringKey() {
    $original = <<<'END'
<?php
variable_get($my_var, TRUE);
END;
    $expected = <<<'END'
<?php
// @FIXME
// The correct configuration object could not be determined. You'll need to
// rewrite this call manually.
variable_get($my_var, TRUE);
END;

    $snippet = Parser::parseSource($original);
    $function_call = $snippet->find(Filter::isFunctionCall('variable_get'))->get(0);
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertNull($rewritten);
    $this->assertSame($expected, $snippet->getText());
  }

  public function testForeignStringKey() {
    $original = <<<'END'
<?php
variable_get('bar_wambooli', TRUE);
END;
    $expected = <<<'END'
<?php
// @FIXME
// This looks like another module's variable. You'll need to rewrite this call
// to ensure that it uses the correct configuration object.
variable_get('bar_wambooli', TRUE);
END;

    $snippet = Parser::parseSource($original);
    $function_call = $snippet->find(Filter::isFunctionCall('variable_get'))->get(0);
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertNull($rewritten);
    $this->assertSame($expected, $snippet->getText());
  }

  public function testStringKeyAndUnextractableDefaultValue() {
    $original = <<<'END'
<?php
variable_get('foo_wambooli', array());
END;
    $expected = <<<'END'
<?php
// @FIXME
// Could not extract the default value because it is either indeterminate, or
// not scalar. You'll need to provide a default value in
// config/install/@module.settings.yml and config/schema/@module.schema.yml.
variable_get('foo_wambooli', array());
END;

    $snippet = Parser::parseSource($original);
    $function_call = $snippet->find(Filter::isFunctionCall('variable_get'))->get(0);
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::config(\'foo.settings\')->get(\'foo_wambooli\')', $rewritten->getText());
    $this->assertSame($expected, $snippet->getText());
  }

  public function testStringKeyAndExtractableDefaultValue() {
    $function_call = Parser::parseExpression('variable_get("foo_wambooli", 30)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::config(\'foo.settings\')->get("foo_wambooli")', $rewritten->getText());
  }

}
