<?php

namespace Drupal\Tests\drupalmoduleupgrader\Unit\Plugin\DMU\Converter\Functions;

use Pharborist\Filter;
use Pharborist\Parser;

/**
 * @group DMU.Converter.Functions
 * @covers \Drupal\drupalmoduleupgrader\Plugin\DMU\Converter\Functions\VariableDel
 */
class VariableDelTest extends FunctionCallModifierTestBase {

  public function testNonStringKey() {
    $original = <<<'END'
<?php
variable_del($my_var);
END;
    $expected = <<<'END'
<?php
// @FIXME
// The correct configuration object could not be determined. You'll need to
// rewrite this call manually.
variable_del($my_var);
END;

    $snippet = Parser::parseSource($original);
    $function_call = $snippet->find(Filter::isFunctionCall('variable_del'))->get(0);
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertNull($rewritten);
    $this->assertSame($expected, $snippet->getText());
  }

  public function testForeignStringKey() {
    $original = <<<'END'
<?php
variable_del('bar_wambooli');
END;
    $expected = <<<'END'
<?php
// @FIXME
// This looks like another module's variable. You'll need to rewrite this call
// to ensure that it uses the correct configuration object.
variable_del('bar_wambooli');
END;

    $snippet = Parser::parseSource($original);
    $function_call = $snippet->find(Filter::isFunctionCall('variable_del'))->get(0);
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertNull($rewritten);
    $this->assertSame($expected, $snippet->getText());
  }

  public function testStringKey() {
    $function_call = Parser::parseExpression('variable_del("foo_wambooli")');
    $rewritten = $this->plugin->rewrite($function_call, $this->target);
    $this->assertInstanceOf('\Pharborist\Objects\ObjectMethodCallNode', $rewritten);
    $this->assertEquals('\Drupal::config(\'foo.settings\')->clear("foo_wambooli")->save()', $rewritten->getText());
  }

}
