<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Filter;
use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\VariableSet
 */
class VariableSetTest extends FunctionCallModifierTestBase {

  public function testNonStringKey() {
    $original = <<<'END'
<?php
variable_set($my_var, TRUE);
END;
    $expected = <<<'END'
<?php
// @FIXME
// The correct configuration object could not be determined. You'll need to
// rewrite this call manually.
variable_set($my_var, TRUE);
END;

    $snippet = Parser::parseSource($original);
    $function_call = $snippet->find(Filter::isFunctionCall('variable_set'))->get(0);
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertNull($rewritten);
    $this->assertSame($expected, $snippet->getText());
  }

  public function testForeignStringKey() {
    $original = <<<'END'
<?php
variable_set('bar_wambooli', TRUE);
END;
    $expected = <<<'END'
<?php
// @FIXME
// This looks like another module's variable. You'll need to rewrite this call
// to ensure that it uses the correct configuration object.
variable_set('bar_wambooli', TRUE);
END;

    $snippet = Parser::parseSource($original);
    $function_call = $snippet->find(Filter::isFunctionCall('variable_set'))->get(0);
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertNull($rewritten);
    $this->assertSame($expected, $snippet->getText());
  }

  public function testStringKey() {
    $function_call = Parser::parseExpression('variable_set("foo_wambooli", 30)');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::configFactory()->getEditable(\'foo.settings\')->set("foo_wambooli", 30)->save()', $rewritten->getText());
  }

}
